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
        "lo_groceries" => array(
            "id",
            "item",
            "id_priority",
            "qty",
            "unit",
            "deadline",
        ),
        "lo_bookmarks" => array(
            "id",
            "link",
            "name",
        ),
        "lo_writing_prompts" => array(
            "id",
            "title",
            "excerpt",
        ),
        "lo_user" => array(
            "id",
            "name",
        ),
        "lo_priority" => array(
            "id",
            "name",
            "priority_num",
        ),
        "lo_tags" => array(
            "id",
            "name",
        ),
        "lo_bookmarks" => array(
            "id",
            "name",
            "link",
        ),
        "lo_wishlist_tags" => array(
            "id",
            "id_wishlist",
            "id_tag",
        ),
        "lo_todo_tags" => array(
            "id",
            "id_todo",
            "id_tag",
        ),
    );

    private static $doneCols = array(
        "lo_todo" => "done",
        "lo_wishlist" => "bought",
    );

    private static $skipMtm = array(
        "lo_tags",
        "lo_bookmarks",
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

    public static function getInstance() 
    {
        if (self::$dbObject == null)
        {
            self::$dbObject = new CDBConfig();
        }
 
        return self::$dbObject->getHandle();
    }

    public static function isValidColumn($module, $column) 
    {
        if (!array_key_exists($module, self::$validCols)) 
        {
            return false;
        }
        return in_array($column, self::$validCols[$module]);
    }

    public static function isValidTable($module) 
    {
        return array_key_exists($module, self::$validCols); 
    }

    public static function hasDoneColumn($module)
    {
        return array_key_exists($module, self::$doneCols); 
    }

    public static function getDoneColumn($module)
    {
        return self::$doneCols[$module]; 
    }

    public static function doSkipMtm($module)
    {
        return array_search($module, self::$skipMtm) !== false; 
    }

}

abstract class OpType 
{
    const searchByColumn = 0;
    const fetchLatest = 1;
    const fetchExt = 2;
    const edit = 3;
    const add = 4;
    const addRaw = 5;
    const del = 6;
    const delRaw = 7;
    const MAX_OP = 8;
};

interface IDBOp 
{
    public function setConnInfo(string $module, string $class);
    public function populateQuery();
    public function setOperationType(int $op);
    public function setOperationParams(array $params);
    public function executeOperation();
    public function getResults() : string;
    public function getRawResults() : ?array;
};

class CQuery
{
    private $statement;
    private $placeholders;

    public function __construct() 
    {
        $this->statement = [];
        $this->placeholders = [];
    }

    public function getStatement() : string
    {
        return join(" ", $this->statement);
    }

    public function getPlaceholders() : array
    {
        return $this->placeholders;
    }

    public function setStatement(string $st)
    {
        $this->statement = [$st];
    }
    public function addStatement(string $st)
    {
        array_push($this->statement, $st);
    }

    public function setPlaceholders(array $pl)
    {
        $this->placeholders = $pl;
    }
    public function addPlaceholder($pl)
    {
        array_push($this->placeholders, $pl);
    }
}

class CDBOperation implements IDBOp
{
    protected $dbo;
    protected $table;
    protected $obj;
    protected $operationType;
    protected $query;

    public function __construct($dbHandle, string $module, ?IDBOp $module_fetcher = null) 
    {
        if ($module != "modules")
        {
            if ($module_fetcher !== null) 
            {
                $module_fetcher->setConnInfo(
                    CDefaultCfg::getCfgItem("default_module_table"), 
                    CDefaultCfg::getCfgItem("default_module_class")
                );
                $module_fetcher->setOperationType(OpType::searchByColumn);
                $module_fetcher->setOperationParams([CDefaultCfg::getCfgItem("default_module_column"), $module]);
                $found_module = $module_fetcher->getRawResults();

                if ($found_module == null || sizeof($found_module) <= 0) 
                {
                    //echo "Warning: module $module not found!";
                }
                else 
                {
                    $found_module = $found_module[0];
                    $this->module = $module;
                    $this->setConnInfo($found_module->getModuleTable(), $found_module->getModuleClass());
                }
            }
        }
        else 
        {
            $this->module = "modules";
            $this->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
            );

        }

        $this->dbo = $dbHandle;

        $this->populateQuery();
    }

    public function setOperationType(int $op) 
    {
        if ($op < 0 || $op >= OpType::MAX_OP)
        {
            return;
        }
        $this->operationType = $op;
    }

    public function setOperationParams(array $pars) 
    {
        return;
    }

    public function executeOperation()    
    {
        //var_dump($this->query->getStatement());
        $stmt = $this->dbo->prepare($this->query->getStatement());
        $stmt->execute($this->query->getPlaceholders());
        return $stmt;
    }

    public function populateQuery()
    {
        $this->query = new CQuery();
    }

    public function setConnInfo(string $table, string $obj) 
    {
        if (CDBConfig::isValidTable($table)) 
        {
            $this->table = $table;
            $this->obj = $obj;
        }
    }

    public function getRawResults() : ?array
    {
        return null;
    }

    public function getResults() : string
    {
        return "";
    }

}

?>