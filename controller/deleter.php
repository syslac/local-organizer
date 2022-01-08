<?php

class CDeleter 
{
    private $table;
    private $dbo;
    private $query;
    private $plH;

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
        else 
        {
            $this->module = "modules";
            $this->setConnInfo(
                CDefaultCfg::getCfgItem("default_module_table"), 
                CDefaultCfg::getCfgItem("default_module_class")
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

    public function run() 
    {
        $query = $this->query["delete"]
            .$this->query["where"];
        $stmt = $this->dbo->prepare($query);
        $stmt->execute($this->plH);

    }
};

?>

