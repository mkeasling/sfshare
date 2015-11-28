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
    <li class="active"><a href="#idb">IDB User Accounts</a></li>
    <li><a href="#sf">Salesforce User Accounts</a></li>
</ul>
<div class="tab-content">
    <div id="idb" class="tab-pane active">
        <?php render('manage_idb'); ?>
    </div>
    <div id="sf" class="tab-pane">
        <?php render('manage_sf'); ?>
    </div>
</div>
<script type="text/javascript">
    (function($){
        $(document).ready(function(){
            $('#maintabs a').click(function (e) {
                e.preventDefault()
                $(this).tab('show')
            })
        });
    }(jQuery));
</script>
