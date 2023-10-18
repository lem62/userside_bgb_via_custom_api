<?php

namespace Lem62\Bgb\Db;

use Lem62\Model\Config;

class MysqlDb
{
    private $db;
    /**
    * @var object $config
    */
    private $config = null;

    public function __construct()
    {
        $this->config = new Config('db');
        $this->db = new \PDO(
            "mysql:host=" . $this->config->db_host . ";" .
            "dbname=" . $this->config->db_name . ";charset=utf8", 
            $this->config->db_user, 
            $this->config->db_pass, 
            array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8")
        );

        $this->db->setAttribute(
            \PDO::ATTR_ERRMODE,
            \PDO::ERRMODE_EXCEPTION
        );
        $this->db->exec("SET NAMES 'utf8';");
    }

    public function __destruct()
    {
        $this->db = null;
    }

    public function __invoke()
    {
        return $this->db;
    }

    public function fetchAll($query)
    {
        return $this->db->query($query)->fetchAll(\PDO::FETCH_ASSOC);
    }
}