<?php

class CEditor 
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
            $this->query["update"] = "UPDATE ".$table;
        }
    }

    public function setData(array $data) 
    {
        $this->query["set"] = " SET ";
        $cnt = 0;
        $all_fields_valid = true;
        foreach ($data as $fld => $val) 
        {
            $all_fields_valid = $all_fields_valid && CDBConfig::isValidColumn($this->table, $fld);
            if ($val == '') 
            {
                $val = null;
            }
            array_push($this->plH, $val);
            if ($cnt == 0) 
            {
                $this->query["set"] .= " ".$fld." = ? ";
            }
            else 
            {
                $this->query["set"] .= ", ".$fld." = ? ";
            }
            $cnt++;
        }
        if (!$all_fields_valid) 
        {
            unset($this->query["set"]);
        }
    }

    public function setCondition(?array $id) 
    {
        if (sizeof($id) == 1) 
        {
            foreach($id as $key => $val)
            {
                if (CDBConfig::isValidColumn($this->table, $key)) 
                {
                    $this->query["where"] = " WHERE ".$key." = ?";
                    array_push($this->plH, $val);
                }
            }
        }
    }

    public function run() 
    {
        $query = $this->query["update"]
            .$this->query["set"]
            .$this->query["where"];
        $stmt = $this->dbo->prepare($query);
        $stmt->execute($this->plH);

    }
};

?>
