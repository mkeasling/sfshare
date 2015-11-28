<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 9:46 PM
 */

namespace Sfshare;

use Mandrill;

class Mail extends Singleton
{
    private $config;
    private $mandrill;

    public function init()
    {
        $this->config = Config::instance()->mandrill;
        $this->mandrill = new Mandrill($this->config['api_key']);
    }

    public function sendToAdmins($subject, $html, $from_email = null, $from_name = null)
    {
        $admins = Database::instance()->query('SELECT DISTINCT email FROM idb_users WHERE is_admin=1 AND is_active=1');
        $emails = array();
        foreach ($admins as $admin) {
            $emails[] = $admin->email;
        }
        $result = $this->send(implode(',', $emails), $subject, $html, $from_email, $from_name);
        error_log(print_r($result,true));
        return $result;
    }

    public function send($to, $subject, $html, $from_email = null, $from_name = null)
    {
        if($from_email===null){
            $from_email = 'sfshare@moretolife.org';
        }
        if($from_name===null){
            $from_name = 'SF Sharing Site';
        }
        $m_to = [];
        foreach (explode(',', str_replace(';', ',', $to)) as $email) {
            $m_to[] = array('email' => $email);
        }
        return $this->mandrill->messages->send(array(
            'html' => $html,
            'subject' => $subject,
            'to' => $m_to,
            'from_email' => $from_email,
            'from_name' => $from_name,
            'track_opens' => false,
            'track_clicks' => false
        ));
    }
}