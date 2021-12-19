<?php

require_once "default_cfg.php";

class CDBConfig 
{
    private $dbo;
    private static $dbObject = null;

    private function __construct() 
    {
        $dsn = "mysql:host=127.0.0.1;dbname=".CDefaultCfg::getCfgItem('db_name').";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->dbo = new PDO($dsn, CDefaultCfg::getCfgItem('db_user'), CDefaultCfg::getCfgItem('db_pass'), $options);
        } catch (\PDOException $e) {
            echo "connection failed";
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    private function getHandle() 
    {
        return $this->dbo;
    }

    public function getInstance() 
    {
        if (self::$dbObject == null)
        {
            self::$dbObject = new CDBConfig();
        }
 
        return self::$dbObject->getHandle();
    }
}

?>