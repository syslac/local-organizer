<?php

require_once "models/base/model_base.php";
require_once "models/modules.php";
require_once "models/wishlist.php";
require_once "models/todo.php";

class CFetcher 
{
    private $results;
    private $table;
    private $foreign;
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

        if (isset($this->dbo) && isset($this->table))
        {
            $this->getForeign();
        }
    }

    public function setConnInfo(string $table, string $obj) 
    {
        $this->table = $table;
        $this->obj = $obj;
    }

    public function getForeign() 
    {
        $stmt = $this->dbo->prepare("
            SELECT `COLUMN_NAME`, `REFERENCED_TABLE_NAME`, `REFERENCED_COLUMN_NAME` 
            FROM `information_schema`.`KEY_COLUMN_USAGE` 
            WHERE `CONSTRAINT_SCHEMA` = ? 
            AND `TABLE_NAME` = ? 
            AND `REFERENCED_TABLE_SCHEMA` IS NOT NULL 
            AND `REFERENCED_TABLE_NAME` IS NOT NULL 
            AND `REFERENCED_COLUMN_NAME` IS NOT NULL
        ");
        $stmt->execute([CDefaultCfg::getCfgItem("db_name"), $this->table]);
        $this->foreign = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaticQuery()
    {
        $query = "SELECT A.*";
        $foreign_counter = 0;
        foreach ($this->foreign as $join) 
        {
            $query .= ", FOREIGN_".$foreign_counter.".name AS ".$join["COLUMN_NAME"]."_ext";
            $foreign_counter++;
        }
        $query .= " FROM ".$this->table." A "; 
        $foreign_counter = 0;
        foreach ($this->foreign as $join) 
        {
            $query .= "LEFT JOIN ".$join["REFERENCED_TABLE_NAME"]." 
            FOREIGN_".$foreign_counter." 
            ON A.".$join["COLUMN_NAME"]." = FOREIGN_".$foreign_counter.".".$join["REFERENCED_COLUMN_NAME"]." ";
            $foreign_counter++;
        }
        return $query;
    }

    public function getLatest(int $limit) 
    {
        $query = $this->getStaticQuery()." ORDER BY id DESC LIMIT ?";
        //var_dump($query);
        $stmt = $this->dbo->prepare($query);
        $stmt->execute([$limit]);
        $this->results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->obj);
    }

    public function searchByColumn(string $column, string $value) : ?object
    {
        if (!CDBConfig::isValidColumn($this->obj, $column)) 
        {
            return null;
        }
        $stmt = $this->dbo->prepare($this->getStaticQuery()." WHERE A.".$column ." = ? LIMIT 0,1");
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