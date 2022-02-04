<?php

include_once "controller/db.php";

class CEditor extends CDBOperation
{
    public function __construct($dbHandle, string $module, ?IDBOp $module_fetcher = null) 
    {
        parent::__construct($dbHandle, $module, $module_fetcher);
        $this->setOperationType(OpType::edit);
        $this->query->setStatement("UPDATE ".$this->table);
    }

    public function setOperationParams(array $pars) 
    {
        switch ($this->operationType)
        {
            case OpType::edit:
                if (sizeof($pars) != 2 || !isset($pars["data"]) || !isset($pars["condition"]))
                {
                    return;
                }
                $this->query->addStatement(" SET ");
                $cnt = 0;
                $all_fields_valid = true;
                foreach ($pars["data"] as $fld => $val) 
                {
                    $all_fields_valid = $all_fields_valid && CDBConfig::isValidColumn($this->table, $fld);
                    if ($val == '') 
                    {
                        $val = null;
                    }
                    $this->query->addPlaceholder($val);
                    if ($cnt == 0) 
                    {
                        $this->query->addStatement(" ".$fld." = ? ");
                    }
                    else 
                    {
                        $this->query->addStatement(", ".$fld." = ? ");
                    }
                    $cnt++;
                }
                if (!$all_fields_valid) 
                {
                    $this->query->setStatement("");
                }
                if (sizeof($pars["condition"]) == 1) 
                {
                    foreach($pars["condition"] as $key => $val)
                    {
                        if (CDBConfig::isValidColumn($this->table, $key)) 
                        {
                            $this->query->addStatement(" WHERE ".$key." = ?");
                            $this->query->addPlaceholder($val);
                        }
                    }
                }
                break;
            default:
                return;
        }
    }  

};

?>