<?php

require_once "default_cfg.php";

class CDBConfig 
{
    private $dbo;
    private static $dbObject = null;
    
    private static $validCols = array(
        "lo_wishlist" => array(
            "id",
            "id_for_user",
            "item",
            "link",
            "price",
            "deadline",
            "bought",
            "id_priority",
        ),
        "lo_todo" => array(
            "id",
            "title",
            "due_date",
            "done",
            "id_priority",
        ),
        "lo_modules" => array(
            "id",
            "module_name",
            "module_table",
            "module_class",
        ),
        "lo_user" => array(
            "id",
            "name",
        ),
        "lo_priority" => array(
            "id",
            "name",
            "priority_num",
        )
    );

    private static $doneCols = array(
        "lo_todo" => "done",
        "lo_wishlist" => "bought",
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

    public function isValidTable($module) 
    {
        return array_key_exists($module, self::$validCols); 
    }

    public function hasDoneColumn($module)
    {
        return array_key_exists($module, self::$doneCols); 
    }

    public function getDoneColumn($module)
    {
        return self::$doneCols[$module]; 
    }
}

?>