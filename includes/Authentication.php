<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 5:17 PM
 */

namespace Sfshare;

use Auth0\SDK\Auth0;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class Authentication extends Singleton
{
    private $config;
    private $auth0;
    private $user;
    private $_db_user;
    private $db;

    protected function init()
    {
        $this->config = Config::instance();
        $this->auth0 = new Auth0($this->config->auth0);
        $this->user = $this->auth0->getUser();
        $this->db = Database::instance();
    }

    public function __get($var)
    {
        switch ($var) {
            case 'user':
                return $this->user;
            case 'db_user':
                if (!isset($this->user) || !$this->user) {
                    throw new NotLoggedInException('You are not logged in.');
                }
                if (!isset($this->_db_user)) {
                    $this->_db_user = $this->db->query_one(
                        'SELECT * FROM local_users WHERE auth0_user_id=? AND username=?',
                        $this->user['user_id'],
                        $this->user['username']
                    );
                }
                return $this->_db_user;
            case 'can_manage':
                if (!$this->db_user) {
                    return false;
                }else{
                    return (!!$this->db_user->is_admin);
                }
            case 'is_logged_in':
            case 'is_logged':
                return (!!$this->user);
            case 'js_block':
                return $this->get_js_block();
            case 'logout_url':
                return 'https://' . $this->config->auth0['domain'] . '/logout?returnTo=' . $this->config->auth0['logout_uri'] . '&client_id=' . $this->config->auth0['client_id'];
            default:
                throw new Exception('Invalid property');
        }
    }

    public function get_salesforce_url()
    {
        if (!$this->is_logged_in) {
            throw new \Exception('You must be logged in to perform this function.');
        }
        // Find the db user (by auth0_user_id and username, first)
        if ($this->db_user === false) {

            // If that's not found, then look by email.
            $user_by_email = $this->db->query_one(
                'SELECT * FROM local_users WHERE email=? AND is_active=1',
                $this->user['email']
            );

            // If that's still not found, then they're a new user.
            // Create a record, send an admin message, explain it to the user.
            if($user_by_email === false){
                $sql = 'INSERT INTO local_users (auth0_user_id, username, email) VALUES (?, ?, ?)';
                $this->db->query($sql, $this->user['user_id'], $this->user['username'], $this->user['email']);

                Mail::instance()->sendToAdmins('New User',$this->getNewUserMessage());
                throw new NewUserException('User not found.');
            }

            // Here, we've found a user record by email, but it doesn't match on auth0_user_id and username.
            // Likely because a record was created before the auth0 registration went through.
            // Update the existing record with these details.
            $this->db->query(
                'UPDATE local_users SET auth0_user_id=?,username=? WHERE email=? AND is_active=1',
                $this->user['user_id'],
                $this->user['username'],
                $this->user['email']
            );
            // Clear our cached db_user object, so it will re-query before continuing.
            unset($this->_db_user);
        }
        if (!$this->db_user->is_active) {
            Mail::instance()->sendToAdmins('New User',$this->getNewUserMessage());
            throw new \Exception('User is not active.');
        }
        $sf_user = $this->db->query_one('SELECT * FROM sf_users WHERE id=?', $this->db_user->sf_user_id);
        if ($sf_user === false) {
            throw new \Exception('Could not retrieve SF account information.');
        }
        $sf_user->password = Crypt::decrypt($sf_user->password);
        $sf_user->security_token = Crypt::decrypt($sf_user->security_token);
        return $this->sf_request($sf_user);
    }

    public function logout()
    {
        $this->auth0->logout();
        http_response_code(301);
        header('Location: ' . $this->logout_url);
    }

    public function get_js_block()
    {
        ob_start();
        js_block();
        $contents = ob_get_clean();
        ob_end_flush();
        return $contents;
    }

    public function js_block()
    {
        ?>
        <script src="https://cdn.auth0.com/js/lock-9.1.min.js"></script>
        <script type="text/javascript">

            var lock = new Auth0Lock('<?php echo $this->config->auth0['client_id']; ?>', '<?php echo $this->config->auth0['domain']; ?>');
            lock.signin = function signin(whichCallback) {
                var callbackUrl;
                switch (whichCallback) {
                    case 'salesforce':
                        callbackUrl = '<?php echo $this->config->auth0['redirect_uri_sf']; ?>';
                        break;
                    case 'manage':
                    default:
                        callbackUrl = '<?php echo $this->config->auth0['redirect_uri']; ?>';
                        break;
                }
                lock.show({
                    callbackURL: callbackUrl,
                    responseType: 'code',
                    authParams: {scope: 'openid profile'}
                });
            }
        </script>
        <?php
    }


    private function sf_request($sf_user)
    {
        $type = $sf_user->type=='sandbox'?'test':'login';
        $address = 'https://'.$type.'.salesforce.com/services/Soap/u/27.0';
        $headers = array('SOAPAction' => 'login', 'Content-type' => 'text/xml');
        $body = <<<EOF
<?xml version="1.0" encoding="utf-8" ?>
<env:Envelope xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:env="http://schemas.xmlsoap.org/soap/envelope/">
  <env:Body>
    <n1:login xmlns:n1="urn:partner.soap.sforce.com">
      <n1:username>{$sf_user->username}</n1:username>
      <n1:password>{$sf_user->password}{$sf_user->security_token}</n1:password>
    </n1:login>
  </env:Body>
</env:Envelope>
EOF;
        $client = new Client();
        try {
            $response = $client->post($address,
                array(
                    'headers' => $headers,
                    'body' => $body
                )
            );
            $body = $response->getBody();
        }catch(ServerException $e){
            error_log($body);
            error_log($e->getResponse()->getBody());
            error_log(print_r($e->getResponse(),true));
            throw $e;
        }catch(\Exception $e){
            error_log(print_r($e->getResponse(),true));
            throw $e;
        }
        $regex = '/^(.*)\<serverUrl\>https\:\/\/(\w+)\.(.*\<sessionId\>)(.*?)(\<\/sessionId\>.*)$/';
        if (!preg_match($regex, $body)) {
            throw new \Exception('Could not parse Salesforce response.');
        }
        $instance = preg_replace($regex, '$2', $body);
        $session_id = preg_replace($regex, '$4', $body);
        return 'https://' . $instance . '.salesforce.com/secur/frontdoor.jsp?sid=' . $session_id;
    }

    private function getNewUserMessage(){
        error_log(print_r($this->user,true));
        return <<<EOF
<h1>New User</h1>
<p>There has been a new SF Share registration for user {$this->user['username']}, with email
<a href='mailto:{$this->user['email']}'>{$this->user['email']}</a>.</p>
<p>Please <a href='http://sfshare.handdipped.biz'>log on</a> at your earliest convenience, to activate them,
and assign them to the appropriate SF user.</p>
<p>Thank you</p>
EOF;
    }
}
