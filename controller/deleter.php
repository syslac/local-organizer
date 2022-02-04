<?php

include_once "controller/db.php";

class CDeleter extends CDBOperation
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
            $this->setOperationType(OpType::delRaw);
        }
        else 
        {
            $this->setOperationType(OpType::del);
        }
        $this->query->setStatement("DELETE FROM ".$this->table);
    }

    public function setOperationParams(array $pars) 
    {
        switch ($this->operationType)
        {
            case OpType::delRaw:
                if (sizeof($pars) == 2
                    && isset($pars["tag"])) 
                {
                    if (CDBConfig::isValidTable($this->table)) 
                    {
                        $this->query->setStatement("DELETE A.* FROM ".$this->table. " A ");
                        $this->query->addStatement(" LEFT JOIN lo_tags T ON T.id = A.id_tag");
                    }
                    else 
                    {
                        $this->query->setStatement("");
                    }
                    $this->query->addStatement(" WHERE ");
                    $clauses = 0;
                    foreach($pars as $key => $val)
                    {
                        $this->query->addStatement($clauses == 0 ? "" : " AND ");
                        $clauses++;
                        if ($key != "tag") 
                        {
                            if (CDBConfig::isValidColumn($this->table, $key)) 
                            {
                                $this->query->addStatement(" A.".$key." = ? ");
                                $this->query->addPlaceholder($val);
                            }
                        }
                        else 
                        {
                            $this->query->addStatement(" T.name LIKE ? ");
                            $this->query->addPlaceholder($val);
                        }
                    }
                }
                break;
            case OpType::del;
                if (sizeof($pars) == 1) 
                {
                    foreach($pars as $key => $val)
                    {
                        if (CDBConfig::isValidColumn($this->table, $key) && $key == "id") 
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