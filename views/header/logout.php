<?php
$auth = \Sfshare\Authentication::instance();
?>
<div class="pull-right" style="line-height: 50px; padding-right: .5em;">
    <div class="navbar-brand"><?php echo $auth->user['username']; ?></div>
    <a href="/logged" class="btn btn-primary">Go to Salesforce</a>
    <?php if($auth->can_manage): ?><a href="/manage" class="btn btn-default">Manage Users</a><?php endif; ?>
    <a href="/logout" class="btn btn-default">Logout</a>
</div>
