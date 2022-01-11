<?php

class CDeleter 
{
    private $table;
    private $dbo;
    private $query;
    private $plH;

    public function __construct($dbHandle, string $module, bool $rawTable = false) 
    {
        if ($module != "modules" && !$rawTable)
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

            if ($found_module == null || sizeof($found_module) <= 0) 
            {
                echo "Warning: module $module not found!";
            }
            else 
            {
                $found_module = $found_module[0];
                $this->setConnInfo($found_module->getModuleTable(), $found_module->getModuleClass());
            }
        }
        else if ($module == "modules")
        {
            $this->module = "modules";
            $this->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
            );
        }
        else if ($rawTable)
        {
            $this->setConnInfo(
                $module, 
                ""
            );
        }

        $this->plH = array();
        $this->dbo = $dbHandle;
    }

    public function setConnInfo(string $table, string $obj) 
    {
        if (CDBConfig::isValidTable($table)) 
        {
            $this->table = $table;
            $this->query["delete"] = "DELETE FROM ".$table;
        }
    }

    public function setCondition(?array $id) 
    {
        if (sizeof($id) == 1) 
        {
            foreach($id as $key => $val)
            {
                if (CDBConfig::isValidColumn($this->table, $key) && $key == "id") 
                {
                    $this->query["where"] = " WHERE ".$key." = ?";
                    array_push($this->plH, $val);
                }
            }
        }
    }

    public function setExternalCondition(?array $conditions) 
    {
        if (sizeof($conditions) == 2
            && isset($conditions["tag"])) 
        {
            if (CDBConfig::isValidTable($this->table)) 
            {
                $this->query["delete"] = "DELETE A.* FROM ".$this->table. " A ";
                $this->query["delete"] .= " LEFT JOIN lo_tags T ON T.id = A.id_tag";
            }
            else 
            {
                $this->query["delete"] = "";
            }
            $this->query["where"] = " WHERE ";
            $clauses = 0;
            foreach($conditions as $key => $val)
            {
                $this->query["where"] .= ($clauses == 0 ? "" : " AND ");
                $clauses++;
                if ($key != "tag") 
                {
                    if (CDBConfig::isValidColumn($this->table, $key)) 
                    {
                        $this->query["where"] .= " A.".$key." = ? ";
                        array_push($this->plH, $val);
                    }
                }
                else 
                {
                    $this->query["where"] .= " T.name LIKE ? ";
                    array_push($this->plH, $val);
                }
            }
        }
    }

    public function run() 
    {
        $query = $this->query["delete"]
            .$this->query["where"];
        $stmt = $this->dbo->prepare($query);
        $stmt->execute($this->plH);

    }
};

?>

