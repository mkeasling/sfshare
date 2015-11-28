<?php
/**
 * Created by IntelliJ IDEA.
 * User: mkeasling
 * Date: 11/27/15
 * Time: 5:17 PM
 */

namespace Sfshare;

class Database extends Singleton
{
    private $config;
    private $db;

    protected function init()
    {
        $this->config = Config::instance()->database;
        $this->db = new \PDO($this->config['dsn'], $this->config['username'], $this->config['password']);
    }

    function query_one()
    {
        $result = call_user_func_array(array($this, 'query'), func_get_args());
        if (!$result) {
            return $result;
        }
        if (count($result) > 1) {
            throw new \Exception('More than one result found.');
        }
        return array_shift($result);
    }

    function query()
    {
        $args = func_get_args();
        if (empty($args) || count($args) < 1) {
            throw new \Exception('Invalid query.');
        }
        $sql = array_shift($args);
        $q = $this->db->prepare($sql);
        if (count($args) > 0 && is_array($args[0])) {
            $args = array_shift($args);
        }
        $q->execute($args);
        $result = $q->fetchAll(\PDO::FETCH_CLASS);
        if (empty($result) || count($result) < 0) {
            return false;
        } else {
            return $result;
        }
    }
}