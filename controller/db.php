<?php

require_once "default_cfg.php";

class CDBConfig 
{
    private $dbo;
    private static $dbObject = null;
    
    private static $validCols = array(
        "CWishlist" => array(
            "id",
            "id_for_user",
            "item",
            "id_priority",
        ),
        "CTodo" => array(
            "id",
            "title",
            "due_date",
            "id_priority",
        ),
        "CModule" => array(
            "id",
            "module_name",
        )
    );

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

    public function isValidColumn($module, $column) 
    {
        if (!array_key_exists($module, self::$validCols)) 
        {
            return false;
        }
        return in_array($column, self::$validCols[$module]);
    }
}

?>