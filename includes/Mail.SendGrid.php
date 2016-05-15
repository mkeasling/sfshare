<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 9:46 PM
 */

namespace Sfshare;

class Mail extends Singleton
{

    private function instantiateMailer()
    {
        $config = Config::instance()->sendgrid;
        $m = new \PHPMailer;
        $m->isSMTP();
        $m->Host = $config['hostname'];
        $m->Port = $config['port'];
        $m->SMTPAutoTLS = false;
        $m->SMTPAuth = true;
        $m->Username = $config['username'];
        $m->Password = $config['password'];
        return $m;
    }

    public function sendToAdmins($subject, $html, $from_email = null, $from_name = null)
    {
        $admins = Database::instance()->query('SELECT DISTINCT email FROM local_users WHERE is_admin=1 AND is_active=1');
        $emails = array();
        foreach ($admins as $admin) {
            $emails[] = $admin->email;
        }
        $result = $this->send(implode(',', $emails), $subject, $html, $from_email, $from_name);
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
        $mailer = self::instantiateMailer();
        $mailer->setFrom($from_email, $from_name);
        foreach (explode(',', str_replace(';', ',', $to)) as $email) {
            $mailer->addAddress($email);
        }
        $mailer->isHTML(true);
        $mailer->Subject = $subject;
        $mailer->Body = $html;
        $result = $mailer->send();
        if(!$result){
            throw new \Exception($mailer->ErrorInfo);
        }
        return $result;
    }
}
