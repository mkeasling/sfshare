<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 5:18 PM
 */

namespace Sfshare;

/**
 * Class Singleton
 */
class Singleton
{
    /**
     * Holds our instances in an array, by class name
     *
     * @var array
     */
    private static $instances = array();
    /**
     * Privatize the __construct() function to prevent "new Singleton()" instantiation.
     */
    private function __construct() {
        if(method_exists($this,'init')){
            $this->init();
        }
    }
    /**
     * Instantiate or return existing instance of the called class
     *
     * Upon instantiation, cache the instance for future use
     *
     * @return mixed
     */
    public static function instance() {
        $class = get_called_class();
        if ( ! isset( self::$instances[$class] ) ) {
            self::$instances[$class] = new $class();
        }
        return self::$instances[$class];
    }
}