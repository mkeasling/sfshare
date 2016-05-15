<?php
if (!\Sfshare\Authentication::instance()->can_manage) {
    throw new \Exception('You do not have permission to manage users.');
}
$db = \Sfshare\Database::instance();
if (!empty($_POST) && !empty($_POST['local_users'])) {
    // get original values for selected users
    $user_ids = array();
    foreach ($_POST['local_users'] as $uid => $user) {
        $user_ids[] = $uid;
    }
    $users_by_id = array();
    $_r = $db->query('SELECT id, email, is_active, sf_user_id FROM local_users WHERE id IN (' . implode(',',$user_ids).')');
    if(!empty($_r)){
        foreach($_r as $user){
            $users_by_id[$user->id] = $user;
        }
    }
    $emails = array();
    $sql = 'REPLACE INTO local_users (id,sf_user_id,is_active,is_admin,auth0_user_id,username,created_date,email) VALUES ';
    $clauses = array();
    $values = array();
    foreach ($_POST['local_users'] as $uid => $user) {
        if ($uid == -1) {
            if (empty($user['email']) || empty($user['sf_user_id'])) {
                continue;
            }
            $uid = null;
        }
        if (isset($user['is_active']) && !empty($user['sf_user_id'])){
            if(isset($users_by_id[$uid])) {
                $orig = $users_by_id[$uid];
                if (!$orig->is_active || empty($orig->sf_user_id)) {
                    $emails[] = $user['email'];
                }
            } else {
                $emails[] = $user['email'];
            }
        }
        $clauses[] = '(?,?,?,?,?,?,?,?)';
        $values[] = $uid;
        $values[] = empty($user['sf_user_id']) ? null : $user['sf_user_id'];
        $values[] = isset($user['is_active']) ? 1 : 0;
        $values[] = isset($user['is_admin']) ? 1 : 0;
        $values[] = $user['auth0_user_id'];
        $values[] = $user['username'];
        $values[] = empty($user['created_date']) ? null : $user['created_date'];
        $values[] = $user['email'];
    }
    $sql .= implode(',', $clauses);
    $db->query($sql, $values);
    if(!empty($emails)){
        \Sfshare\Mail::instance()->send(implode(',', $emails), 'Account activated',
        '<p>Your request to use a shared SF login has been approved.</p><p>Please <a href="http://sfshare.handdipped.biz">log on now</a>.</p>');
    }
    header('Location: /manage');
}
$users = $db->query('SELECT * FROM local_users ORDER BY is_active DESC, username ASC, email ASC');
// $users[] = json_decode('{"id":-1,"is_active":null,"is_admin":null,"sf_user_id":null,"auth0_user_id":null,"username":null,"created_date":null,"updated_date":null,"email":null}');
$sfusers = $db->query('SELECT id, username FROM sf_users WHERE is_active=1 ORDER BY username');
$sfusermap = array();
foreach($sfusers as $sf){
    $sfusermap[$sf->id] = $sf->username;
}
?>
<form method="POST">
    <table class="table">
        <thead>
        <tr>
            <th>Username</th>
            <th>Email</th>
            <th>SF Account</th>
            <th>Active?</th>
            <th>Admin?</th>
            <th>Created Date</th>
            <th>Last Updated Date</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td>
                    <?php echo $user->username ?: '<i>new user</i>'; ?>
                    <input
                        type="hidden"
                        name="local_users[<?php echo $user->id; ?>][auth0_user_id]"
                        value="<?php echo $user->auth0_user_id; ?>"/>
                    <input
                        type="hidden"
                        name="local_users[<?php echo $user->id; ?>][username]"
                        value="<?php echo $user->username; ?>"/>
                    <input
                        type="hidden"
                        name="local_users[<?php echo $user->id; ?>][created_date]"
                        value="<?php echo $user->created_date; ?>"/>
                </td>
                <td>
                    <div class="hidden"><?php echo $user->email; ?></div>
                    <input
                        type="email"
                        name="local_users[<?php echo $user->id; ?>][email]"
                        value="<?php echo $user->email; ?>"
                        />
                </td>
                <td>
                    <div class="hidden"><?php echo isset($sfusermap[$user->sf_user_id])?$sfusermap[$user->sf_user_id]:''; ?></div>
                    <select
                        name="local_users[<?php echo $user->id; ?>][sf_user_id]"
                        value="<?php echo $user->sf_user_id; ?>">
                        <option value="">-- Select a SF User --</option>
                        <?php foreach ($sfusers as $sf): ?>
                            <option
                                value="<?php echo $sf->id; ?>"
                                <?php if ($sf->id == $user->sf_user_id): ?>selected="selected"<?php endif; ?>
                                ><?php echo $sf->username; ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
                <td>
                    <div class="hidden"><?php echo $user->is_active?'a':'b'; ?></div>
                    <input
                        type="checkbox"
                        name="local_users[<?php echo $user->id; ?>][is_active]"
                        value="1"
                        <?php if ($user->is_active): ?>checked="checked"<?php endif; ?>
                        />
                </td>
                <td>
                    <div class="hidden"><?php echo $user->is_admin?'a':'b'; ?></div>
                    <input
                        type="checkbox"
                        name="local_users[<?php echo $user->id; ?>][is_admin]"
                        value="1"
                        <?php if ($user->is_admin): ?>checked="checked"<?php endif; ?>
                        />
                </td>
                <td><?php echo $user->created_date; ?></td>
                <td><?php echo $user->updated_date; ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div>
        <input type="submit" name="local_submit" class="btn btn-primary" value="Save Changes"/>
    </div>
</form>

<hr />

<h2>New User</h2>

<form method='POST' class='row'>
<input type='hidden' name='local_users[-1][auth0_user_id]' value='' />
<input type='hidden' name='local_users[-1][username]' value='' />
<input type='hidden' name='local_users[-1][created_date]' value='' />
<div class='col-sm-6'>
<fieldset class='form-group'>
    <label for='local_users[-1][email]'>Email:</label>
    <input type='email' name='local_users[-1][email]' value='' class='form-control' />
</fieldset>
<fieldset class='form-group'>
    <label for='local_users[-1][sf_user_id]'>SF User:</label>
    <select
	name="local_users[-1][sf_user_id]"
	value=""
    class='form-control'>
	<option value="">-- Select a SF User --</option>
	<?php foreach ($sfusers as $sf): ?>
	    <option
		value="<?php echo $sf->id; ?>"
		><?php echo $sf->username; ?></option>
	<?php endforeach; ?>
    </select>
</fieldset>
<fieldset class='form-group form-inline'>
    <label for='local_users[-1][is_active]'>Activate?:</label>
    <input type='checkbox' name='local_users[-1][is_active]' value='1' class='checkbox' />
</fieldset>
<fieldset class='form-group form-inline'>
    <label for='local_users[-1][is_admin]'>Make Admin?:</label>
    <input type='checkbox' name='local_users[-1][is_admin]' value='1' class='checkbox' />
</fieldset>
<fieldset class='form-group'>
    <input type="submit" name="local_submit" class="btn btn-primary" value="Save Changes"/>
</fieldset>
</div>
</form>
