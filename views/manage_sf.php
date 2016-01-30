<?php
if (!\Sfshare\Authentication::instance()->can_manage) {
    throw new \Exception('You do not have permission to manage users.');
}
$db = \Sfshare\Database::instance();
if (!empty($_POST) && !empty($_POST['sf_users']) && isset($_POST['sf_submit'])) {
    $statements = [];
    $values = [];
    foreach ($_POST['sf_users'] as $uid => $user) {
        if (!isset($user['is_active'])) {
            $user['is_active'] = 0;
        }
        if (!empty($user['password'])) {
            $user['password'] = \Sfshare\Crypt::encrypt($user['password']);
        }
        if (!empty($user['security_token'])) {
            $user['security_token'] = \Sfshare\Crypt::encrypt($user['security_token']);
        }
        $sql = '';
        if ($uid > 0) {
            $sql = 'UPDATE sf_users SET ';
            $clauses = [];
            foreach ($user as $key => $val) {
                $clauses[] = "`{$key}`=?";
                $values[] = $val;
            }
            $sql .= implode(',', $clauses);
            $sql .= ' WHERE id=?';
            $values[] = $uid;
        } elseif (!empty($user['username']) && !empty($user['password']) && !empty($user['security_token'])) {
            $sql = 'INSERT INTO sf_users (';
            $fields = [];
            $places = [];
            foreach ($user as $key => $val) {
                $fields[] = "`{$key}`";
                $places[] = '?';
                $values[] = $val;
            }
            $sql .= implode(',', $fields) . ') VALUES (' . implode(',', $places) . ')';
        }
        $statements[] = $sql;
    }
    $sql = implode(';', $statements);
//    error_log($sql);
//    error_log(print_r($values,true));
    $db->query($sql, $values);
    header('Location: /manage');
}
$users = $db->query('SELECT * FROM sf_users ORDER BY type, username');
$users[] = json_decode('{"id":-1,"username":null,"password":null,"security_token":null,"is_active":null,"type":"production"}');
?>
<form method="POST">
    <table class="table">
        <thead>
        <tr>
            <th>Username</th>
            <th>Password</th>
            <th>Security Token</th>
            <th>Active?</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($users as $user): ?>
            <?php
            /**/
            $user->password = \Sfshare\Crypt::decrypt($user->password);
            $user->security_token = \Sfshare\Crypt::decrypt($user->security_token);
            /**/
            ?>
            <tr>
                <td>
                    <input
                        type="text"
                        name="sf_users[<?php echo $user->id; ?>][username]"
                        value="<?php echo $user->username; ?>"
                        <?php if (!empty($user->username)): ?>readonly="readonly"<?php endif; ?>
                        />
                </td>
                <td>
                    <input
                        type="text"
                        name="sf_users[<?php echo $user->id; ?>][password]"
                        value="<?php echo $user->password; ?>"/>
                </td>
                <td>
                    <input
                        type="text"
                        name="sf_users[<?php echo $user->id; ?>][security_token]"
                        value="<?php echo $user->security_token; ?>"/>
                </td>
                <td><input
                        type="checkbox"
                        name="sf_users[<?php echo $user->id; ?>][is_active]"
                        value="1"
                        <?php if ($user->is_active): ?>checked="checked"<?php endif; ?>
                        />
                </td>
                <td>
                    <select name="sf_users[<?php echo $user->id; ?>][type]">
                        <option
                            value="production"
                            <?php if ($user->type == 'production'): ?>selected="selected"<?php endif; ?>
                            >Production
                        </option>
                        <option
                            value="sandbox"
                            <?php if ($user->type == 'sandbox'): ?>selected="selected"<?php endif; ?>
                            >Sandbox
                        </option>
                    </select>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div>
        <input type="submit" name="sf_submit" class="btn btn-primary" value="Save Changes"/>
    </div>
</form>