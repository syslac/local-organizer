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
    private $module;
    private $mtm;
    private $obj;
    private $dbo;
    private $query;
    private $searchById;

    public function __construct($dbHandle, string $module, bool $getForeign = true, bool $getMtm = true) 
    {
        if ($module != "modules")
        {
            $fetch_module = new CFetcher($dbHandle, 'modules');
            $fetch_module->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
            );
            $fetch_module->populateQuery();
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
                $this->module = $module;
                $this->setConnInfo($found_module->getModuleTable(), $found_module->getModuleClass());
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

        if (isset($this->dbo) && isset($this->table))
        {
            if ($getForeign)
            {
                $this->getForeign();
            }
            if ($getMtm)
            {
                $this->getManyToMany();
            }
        }
        $this->populateQuery();
        $this->searchById = false;
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
            WHERE `TABLE_SCHEMA` = ? 
            AND `TABLE_NAME` = ? 
            AND `REFERENCED_TABLE_SCHEMA` IS NOT NULL 
            AND `REFERENCED_TABLE_NAME` IS NOT NULL 
            AND `REFERENCED_COLUMN_NAME` IS NOT NULL
        ");
        $stmt->execute([CDefaultCfg::getCfgItem("db_name"), $this->table]);
        $this->foreign = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getManyToMany() 
    {
        $stmt = $this->dbo->prepare("
            SELECT TABLE_TO_MTM.`COLUMN_NAME` AS MTM_ID, TABLE_TO_MTM.`REFERENCED_COLUMN_NAME` AS MTM_ORIG_ID, MTM_TO_FOREIGN.`TABLE_NAME`, MTM_TO_FOREIGN.`COLUMN_NAME`, MTM_TO_FOREIGN.`REFERENCED_TABLE_NAME`, MTM_TO_FOREIGN.`REFERENCED_COLUMN_NAME` 
            FROM `information_schema`.`KEY_COLUMN_USAGE` TABLE_TO_MTM
            LEFT JOIN `information_schema`.`KEY_COLUMN_USAGE` MTM_TO_FOREIGN 
                ON TABLE_TO_MTM.`TABLE_NAME` = MTM_TO_FOREIGN.`TABLE_NAME`
                AND TABLE_TO_MTM.`REFERENCED_TABLE_NAME` <> MTM_TO_FOREIGN.`REFERENCED_TABLE_NAME`
            WHERE TABLE_TO_MTM.`TABLE_SCHEMA` = ?
            AND MTM_TO_FOREIGN.`TABLE_SCHEMA` = ?
            AND TABLE_TO_MTM.`REFERENCED_TABLE_NAME` = ?
            AND TABLE_TO_MTM.`REFERENCED_TABLE_SCHEMA` IS NOT NULL 
            AND TABLE_TO_MTM.`REFERENCED_TABLE_NAME` IS NOT NULL 
            AND TABLE_TO_MTM.`REFERENCED_COLUMN_NAME` IS NOT NULL
        ");
        $stmt->execute([CDefaultCfg::getCfgItem("db_name"), CDefaultCfg::getCfgItem("db_name"), $this->table]);
        $this->mtm = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function populateQuery()
    {
        $q_select = "SELECT A.*";
        $foreign_counter = 0;
        foreach ($this->foreign as $join) 
        {
            $q_select .= ", FOREIGN_".$foreign_counter.".name AS ".$join["COLUMN_NAME"]."_ext";
            $foreign_counter++;
        }
        $mtm_counter = 0;
        foreach ($this->mtm as $join) 
        {
            $q_select .= ", GROUP_CONCAT(MTMEND_".$mtm_counter.".name) AS ".$join["COLUMN_NAME"]."_mtm";
            $mtm_counter++;
        }
        $q_select .= " FROM ".$this->table." A "; 
        $foreign_counter = 0;
        $q_join = "";
        foreach ($this->foreign as $join) 
        {
            $q_join .= "LEFT JOIN ".$join["REFERENCED_TABLE_NAME"]." 
            FOREIGN_".$foreign_counter." 
            ON A.".$join["COLUMN_NAME"]." = FOREIGN_".$foreign_counter.".".$join["REFERENCED_COLUMN_NAME"]." ";
            $foreign_counter++;
        }
        $mtm_counter = 0;
        foreach ($this->mtm as $join) 
        {
            $q_join .= "LEFT JOIN ".$join["TABLE_NAME"]." 
            MTMMIDDLE_".$mtm_counter." 
            ON A.".$join["MTM_ORIG_ID"]." = MTMMIDDLE_".$mtm_counter.".".$join["MTM_ID"]."
            LEFT JOIN ".$join["REFERENCED_TABLE_NAME"]."
            MTMEND_".$mtm_counter."
            ON MTMMIDDLE_".$mtm_counter.".".$join["COLUMN_NAME"]." = MTMEND_".$mtm_counter.".".$join["REFERENCED_COLUMN_NAME"]."
            ";
            $mtm_counter++;
        }
        $q_group = "";
        if (sizeof($this->mtm) > 0) 
        {
            $q_group .= " GROUP BY A.id ";
        }
        $this->query = array(
           "select" => $q_select,
           "join" => $q_join,
           "where" => "",
           "group" => $q_group,
           "order" => $q_order,
        );
    }

    public function getLatest(int $limit) 
    {
        $query = $this->query["select"]
            .$this->query["join"]
            .$this->query["where"]
            .$this->query["group"]
            ." ORDER BY A.id DESC LIMIT ?";
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
        $query = $this->query["select"]
            .$this->query["join"]
            ." WHERE A.".$column ." = ? "
            .$this->query["group"]
            ." LIMIT 0,1 ";

        $stmt = $this->dbo->prepare($query);
        $stmt->execute([$value]);
        $this->results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->obj);
        if ($column == "id") 
        {
            $this->searchById = true;
        }
        if (sizeof($this->results) > 0) 
        {
            return $this->results[0];
        }
        else return new stdClass();
    }

    public function getResults(): string
    {
        if ($this->searchById) 
        {
            if (sizeof($this->results) > 0)
            {
                return json_encode(["mode" => "edit", "module" => $this->module, "data" => $this->results]);
            }
            else 
            {
                return json_encode(["mode" => "edit", "module" => $this->module, "data" => [new $this->obj]]);
            }
        }
        else 
        {
            return json_encode(["mode" => "view", "data" => $this->results]);
        }
    }
};

?>