<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 6:08 PM
 */
$auth = \Sfshare\Authentication::instance();
if ($auth->is_logged_in){
    if($auth->can_manage){
        render('manage');
    }else {
        ?>
        <div class="jumbotron">
            <p>
                You're logged in, but don't have permission to do much.
            </p>

            <p>
                <a href="/logged" class="btn btn-primary">Go to Salesforce</a>
                <a href="/logout" class="btn btn-default">Logout</a>
            </p>
        </div>
        <?php
    }
} else {
    ?>
    <div class="jumbotron">
        <h1><?php echo \Sfshare\Config::instance()->site['title']; ?></h1>

        <p>Click here to login and go to Salesforce:</p>

        <p>
            <button class="btn btn-primary" onclick="window.lock.signin('salesforce');">Login to Salesforce</button>
        </p>
    </div>
    <?php
}
