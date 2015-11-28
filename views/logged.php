<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 3:22 PM
 */
namespace Sfshare;

$auth = Authentication::instance();
try{
    $sfhref = $auth->get_salesforce_url();
    http_response_code(301);
    header('Location: '.$sfhref);
}catch(NewUserException $e){
    ?>
    <div class="alert alert-info">
        <h2>New User Created</h2>
        <p>Your account has been created, and is awaiting approval by an admin.</p>
        <p>Once this is complete, you can come back to this site and login to Salesforce.</p>
    </div>
<?php
}
