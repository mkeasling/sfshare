<?php
if(!\Sfshare\Authentication::instance()->can_manage){
    throw new \Exception('You do not have permission to manage users.');
}
if(!empty($_POST) && isset($_POST['mail'])){
    \Sfshare\Mail::instance()->send($_POST['to'],$_POST['subject'],$_POST['html']);
}
?>
<h2>Manage Users</h2>
<ul id="maintabs" class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#local">User Accounts</a></li>
    <li><a href="#sf">Salesforce Accounts</a></li>
    <li><a href="#help">Help</a></li>
</ul>
<div class="tab-content">
    <div id="local" class="tab-pane active">
        <?php render('manage_local'); ?>
    </div>
    <div id="sf" class="tab-pane">
        <?php render('manage_sf'); ?>
    </div>
    <div id="help" class="tab-pane">
        <h2>User Accounts</h2>
        <p>
            This is where you manage access for individual users.  Users can be entered into the system in one
            of two ways.
            <ol>
                <li>
                    You can create new users from the "User Accounts" tab at any time.  If you create an account
                    this way, you must match the email address exactly to what the user enters later, when they
                    register with the site.
                </li>
                <li>
                    The user creates his or her own account when they first register on the site.  If the user
                    registers an email address that is not already in our system, then all of the admin users
                    will receive an email asking them to activate the new user account, and assign them a SF user.
                </li>
            </ol>
        </p>
        <p>
            At any point in time, you can update users by modifying their email (which will ONLY effect communications
            on this password sharing site), re-assigning them to a different SF account, de-activating them, or
            changing their admin status (which is for this password sharing site ONLY).
        </p>
        <p class="alert alert-danger">
            All admins on this site have permission to do everything, so that privilege should be given VERY sparingly.
            Permissions include seeing, modifying, activating/deactivating users and Salesforce accounts,
            and granting/removing admin privileges.
        </p>
        <h2>Salesforce Accounts</h2>
        <p>
            This is where you manage the Salesforce account credentials for the actual SF user accounts.  Changes
            made here do <strong>NOT</strong> update Salesforce, or vice versa.  Any changes made in Salesforce must
            be manually copied here, before the logins will work for those accounts.
        </p>
        <p>
            The Production / Sandbox dropdown should almost always be set to Production.  The sandboxes are for
            development only, and the developers should usually log in to them directly.
        </p>
        <p class="alert alert-danger">
            Mis-configuring or disabling a Salesforce account will render it unusable for ALL people sharing that
            account!  If that happens, you will have to log into the SF account directly, find the correct credentials,
            fix the account on this site, and possibly re-map all appropriate users to the account.
        </p>
    </div>
</div>
<link rel="stylesheet" href="//cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css" />
<script type="text/javascript" src="//cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $('#maintabs a').click(function (e) {
                e.preventDefault()
                $(this).tab('show')
            });
            $('table.table').DataTable();
        });
    }(jQuery));
</script>
