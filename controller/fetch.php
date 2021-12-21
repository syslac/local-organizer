<?php

require_once "models/base/model_base.php";
require_once "models/modules.php";
require_once "models/wishlist.php";
require_once "models/todo.php";

class CFetcher 
{
    private $results;
    private $table;
    private $obj;
    private $dbo;

    public function __construct($dbHandle, string $module) 
    {
        if ($module != "modules")
        {
            $fetch_module = new CFetcher($dbHandle, 'modules');
            $fetch_module->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
            );
            $found_module = $fetch_module->searchByColumn(
                CDefaultCfg::getCfgItem("default_module_column"), 
                $module
            );

            if ($found_module == null) 
            {
                echo "Warning: module $module not found!";
            }
            else 
            {
                $this->setConnInfo($found_module->getModuleTable(), $found_module->getModuleClass());
            }
        }
        else 
        {
            $this->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
            );

        }

        $this->dbo = $dbHandle;
    }

    public function setConnInfo(string $table, string $obj) 
    {
        $this->table = $table;
        $this->obj = $obj;
    }

    public function getLatest(int $limit) 
    {
        $stmt = $this->dbo->prepare("SELECT * FROM ".$this->table." ORDER BY id DESC LIMIT ?");
        $stmt->execute([$limit]);
        $this->results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->obj);
    }

    public function searchByColumn(string $column, string $value) : ?object
    {
        if (!CDBConfig::isValidColumn($this->obj, $column)) 
        {
            return null;
        }
        $stmt = $this->dbo->prepare("SELECT * FROM ".$this->table." WHERE ".$column ." = ? LIMIT 0,1");
        $stmt->execute([$value]);
        $this->results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->obj);
        if (sizeof($this->results) > 0) 
        {
            return $this->results[0];
        }
        else return [];
    }

    public function getResults(): string
    {
        return json_encode($this->results);
    }
};

?>