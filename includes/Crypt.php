<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/28/15
 * Time: 10:42 AM
 */

namespace Sfshare;


class Crypt
{

    public static function encrypt($data){
        error_log('encrypt: '.$data);
        $key = Config::instance()->security['encryption_key'];
        $key = hash('sha256', $key, true);

        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_DEV_URANDOM);
        mcrypt_generic_init($td, $key, $iv);
        $encrypted_data = mcrypt_generic($td, $data);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return base64_encode($encrypted_data.'::'.$iv);
    }

    public static function decrypt($data){
        $key = Config::instance()->security['encryption_key'];
        $key = hash('sha256', $key, true);
        $data = base64_decode($data);
        if(false===strpos($data,'::')){
            return $data;
        }
        list($input,$iv) = explode('::',$data);
        if(!$input || !$iv) return $data;

        $td = mcrypt_module_open('rijndael-256', '', 'ofb', '');
        mcrypt_generic_init($td, $key, $iv);
        $unencrypted_data = mdecrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        return $unencrypted_data;
    }
}