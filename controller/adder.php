<?php

class CAdder 
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
        $this->table = $table;
        $this->query["insert"] = "INSERT INTO ".$table;
    }

    public function setData(array $data) 
    {
        $cnt = 0;
        $fields = "";
        $places = "";
        foreach ($data as $fld => $val) 
        {
            if ($val == '') 
            {
                $val = null;
            }
            array_push($this->plH, $val);
            if ($cnt == 0) 
            {
                $fields .= " (".$fld."";
                $places .= " (?";
            }
            else 
            {
                $fields .= ", ".$fld."";
                $places .= ", ?";
            }
            $cnt++;
        }
        $fields .= ")";
        $places .= ")";
        $this->query["set"] = $fields." VALUES ".$places;
    }

    public function run() 
    {
        $query = $this->query["insert"]
            .$this->query["set"];
        //var_dump($query);
        $stmt = $this->dbo->prepare($query);
        $stmt->execute($this->plH);

    }
};

?>

