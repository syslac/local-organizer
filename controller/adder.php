<?php

include_once "controller/db.php";

class CAdder extends CDBOperation
{
    public function __construct($dbHandle, string $module, ?IDBOp $module_fetcher = null, bool $rawTable = false) 
    {
        parent::__construct($dbHandle, $module, $module_fetcher);
        if ($rawTable)
        {
            $this->setConnInfo(
                $module, 
                ""
            );
            $this->setOperationType(OpType::addRaw);
        }
        else 
        {
            $this->setOperationType(OpType::add);
        }
        $this->query->setStatement("INSERT INTO ".$this->table);
    }

    public function setOperationParams(array $pars) 
    {
        switch ($this->operationType)
        {
            case OpType::addRaw:
            case OpType::add:
                $cnt = 0;
                $fields = "";
                $places = "";
                $all_fields_valid = true;
                foreach ($pars as $fld => $val) 
                {
                    $all_fields_valid = $all_fields_valid && CDBConfig::isValidColumn($this->table, $fld);
                    if ($val == '') 
                    {
                        $val = null;
                    }
                    $this->query->addPlaceholder($val);
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
                if ($all_fields_valid) 
                {
                    $this->query->addStatement($fields." VALUES ".$places);
                }
                break;
            default:
                return;
        }
        
    }
};

?>

