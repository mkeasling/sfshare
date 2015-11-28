<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 6:26 PM
 */

namespace Sfshare;


class View extends Singleton
{
    private $vars = array();

    public function __get($var){
        return $this->vars[$var];
    }

    public function __set($var,$val){
        $this->vars[$var] = $val;
    }
}