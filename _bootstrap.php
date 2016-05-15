<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 5:19 PM
 */

if (!defined('BASE')) {
    define('BASE', __DIR__);
}

require_once 'vendor/autoload.php';

use Sfshare\Config;
use Sfshare\Authentication;

set_exception_handler(function (\Exception $e) {
    \Sfshare\View::instance()->exception = $e;
    render('error');
});
set_error_handler(function ($errno, $errstr, $errfile = null, $errline = null, $errcontext = null) {
    throw new \Exception($errstr);
});

$config = Config::instance();
$config->load(BASE . '/config.yml');
$auth = Authentication::instance();

function get_route()
{
    $request = $_SERVER['REQUEST_URI'];
    if (substr($request, 0, 1) === '/') {
        $request = substr($request, 1);
    }
    $request = preg_replace('/\?.*$/', '', $request);
    if (preg_match('/\w+\.\w+/', $request)) {
        die();
    }
    return $request;
}

function render($file, $backup = null)
{
    $file = trim($file);
    if (strlen($file) < 1) {
        if ($backup !== null) {
            render($backup);
        }
        return;
    }
    if (substr($file, 0, 1) == '/') {
        $file = substr($file, 1);
    }
    $file = BASE . '/views/' . $file;
    if (substr($file, -3) !== '.php') {
        $file .= '.php';
    }
    if (!file_exists($file)) {
        if ($backup !== null) {
            render($backup);
        } else {
            throw new Exception('Cannot find file ' . $file);
        }
    }
    include($file);
}

