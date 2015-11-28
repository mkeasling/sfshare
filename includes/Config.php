<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 5:18 PM
 */

namespace Sfshare;

use Symfony\Component\Yaml\Yaml;

class Config extends Singleton
{
    /**
     * @var array
     */
    private $_config = array();

    public function __get($var){
        return $this->_config[$var];
    }

    public function __set($var,$val){
        $this->_config[$var] = $val;
    }

    public function load($file){
        if(!file_exists($file)){
            throw new Exception('Invalid config file.');
        }
        $this->_config = Yaml::parse(file_get_contents($file));
    }

}