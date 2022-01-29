<?php

require_once "controller/db.php";
require_once "controller/relation.php";
require_once "models/base/model_base.php";
require_once "models/modules.php";
require_once "models/wishlist.php";
require_once "models/todo.php";
require_once "models/tags.php";
require_once "models/bookmarks.php";

class CFetcher extends CDBOperation
{
    private $results;
    private $searchById;
    private $pdoFetchMode;
    private $searchColError;
    private $rel_finder;

    public function __construct($dbHandle, string $module, ?IDBOp $module_fetcher = null, ?IRelationFinder $rel = null) 
    {
        parent::__construct($dbHandle, $module, $module_fetcher);

        if (isset($this->dbo) && isset($this->table))
        {
            $rel->init($this->table, $this->dbo, CDBConfig::doSkipMtm($this->table));
        }
        $this->searchById = false;
        $this->pdoFetchMode = PDO::FETCH_CLASS;
        $this->searchColError = false;
        $this->rel_finder = $rel;
    }

    public function setConnInfo(string $table, string $obj) 
    {
        if (CDBConfig::isValidTable($table)) 
        {
            $this->table = $table;
            $this->obj = $obj;
        }
    }

    public function setSearchColumn() 
    {
        $this->setOperationType(OpType::searchByColumn);
    }

    public function setGetLatest() 
    {
        $this->setOperationType(OpType::fetchLatest);
    }

    public function setFetchExt() 
    {
        $this->setOperationType(OpType::fetchExt);
    }

    public function getResetSearchColError() : bool
    {
        $ret = $this->searchColError;
        $this->searchColError = false;
        return $ret;
    }

    public function setOperationParams(array $pars) 
    {
        $q_order = "";
        $q_select = "SELECT A.*";
        if (
            CDBConfig::hasDoneColumn($this->table)
            && CDBConfig::isValidColumn($this->table, CDBConfig::getDoneColumn($this->table))
        )
        {
            $q_select .= ", IFNULL(A.".CDBConfig::getDoneColumn($this->table).", ADDDATE(NOW(), INTERVAL 1 DAY)) AS doneSortable ";
            $q_order = " ORDER BY doneSortable DESC, A.id DESC";
        }
        else 
        {
            $q_order = " ORDER BY A.id DESC ";
        }
        $foreign_counter = 0;
        $mtm_counter = 0;
        if (isset($this->rel_finder))
        {
            ;
            for ($next_relation = $this->rel_finder->GetNextDirectRelation(); $next_relation !== null; $next_relation = $this->rel_finder->GetNextDirectRelation())
            {
                $q_select .= ", FOREIGN_".$foreign_counter.".name AS ".$next_relation->local_col."_ext";
                $foreign_counter++;
            } 

            for ($next_relation = $this->rel_finder->GetNextMtmRelation(); $next_relation !== null; $next_relation = $this->rel_finder->GetNextMtmRelation())
            {
                $q_select .= ", GROUP_CONCAT(MTMEND_".$mtm_counter.".name) AS ".$next_relation->mtm_middle_col_to."_mtm";
                $mtm_counter++;
            } 
            $this->rel_finder->resetIterators();
        }
        $q_select .= " FROM ".$this->table." A "; 
        $foreign_counter = 0;
        $mtm_counter = 0;
        $q_join = "";
        if (isset($this->rel_finder))
        {
            for ($next_relation = $this->rel_finder->GetNextDirectRelation(); $next_relation !== null; $next_relation = $this->rel_finder->GetNextDirectRelation())
            {
                $q_join .= "LEFT JOIN ".$next_relation->foreign_table." 
                FOREIGN_".$foreign_counter." 
                ON A.".$next_relation->local_col." = FOREIGN_".$foreign_counter.".".$next_relation->foreign_col." ";
                $foreign_counter++;
            }

            for ($next_relation = $this->rel_finder->GetNextMtmRelation(); $next_relation !== null; $next_relation = $this->rel_finder->GetNextMtmRelation())
            {
                $q_join .= "LEFT JOIN ".$next_relation->mtm_middle_table." 
                MTMMIDDLE_".$mtm_counter." 
                ON A.".$next_relation->local_col." = MTMMIDDLE_".$mtm_counter.".".$next_relation->mtm_middle_col_from."
                LEFT JOIN ".$next_relation->foreign_table."
                MTMEND_".$mtm_counter."
                ON MTMMIDDLE_".$mtm_counter.".".$next_relation->mtm_middle_col_to." = MTMEND_".$mtm_counter.".".$next_relation->foreign_col."
                ";
                $mtm_counter++;
            } 

            $this->rel_finder->resetIterators();
        }
        $q_group = "";
        if ($this->rel_finder->getNumMtmRelations() > 0) 
        {
            $q_group .= " GROUP BY A.id ";
        }

        switch ($this->operationType)
        {
            case OpType::searchByColumn:
                $this->pdoFetchMode = PDO::FETCH_CLASS;
                if (sizeof($pars) != 2)
                {
                    return;
                }
                list($column, $value) = $pars;

                if ($column == "id") 
                {
                    $this->searchById = true;
                }

                $innerWhere = " WHERE 1 ";
                $outerWhere = " WHERE 1";
                if (!CDBConfig::isValidColumn($this->table, $column)) 
                {
                    $found_mtm = false;
                    if (isset($this->rel_finder))
                    {
                        do 
                        {
                            $next_relation = $this->rel_finder->GetNextMtmRelation();
                            if ($column === $next_relation->mtm_middle_col_to."_mtm") 
                            {
                                $found_mtm = true;
                                break;
                            }
                        } while ($next_relation !== null);
                        $this->rel_finder->resetIterators();
                    }
                    if (!$found_mtm)
                    {
                        $this->searchColError = true;
                    }
                    else 
                    {
                        $outerWhere = " WHERE Q.".$column ." LIKE CONCAT('%', ?, '%') ";
                    }
                }
                else 
                {
                    $innerWhere = " WHERE A.".$column ." = ? ";
                }
                $this->query->setStatement("SELECT * FROM (");
                $this->query->addStatement($q_select);
                $this->query->addStatement($q_join);
                $this->query->addStatement($innerWhere);
                $this->query->addStatement($q_group);
                $this->query->addStatement(" ) Q ".$outerWhere." ");
                $this->query->setPlaceholders([$value]);
                break;
            case OpType::fetchLatest:
                $this->pdoFetchMode = PDO::FETCH_CLASS;
                $this->query->setStatement($q_select);
                $this->query->addStatement($q_join);
                $this->query->addStatement("");
                $this->query->addStatement($q_group);
                $this->query->addStatement($q_order);
                $this->query->addStatement(" LIMIT ? ");
                $this->query->setPlaceholders([$pars[0]]);
                break;
            case OpType::fetchExt:
                $this->pdoFetchMode = PDO::FETCH_ASSOC;
                $this->query->setStatement($q_select);
                $this->query->addStatement($q_join);
                $this->query->addStatement("");
                $this->query->addStatement($q_group);
                break;
            default:
                return;
        }
    }

    public function getRawResults(): array
    {
        if ($this->pdoFetchMode == PDO::FETCH_CLASS) 
        {
            $this->results = $this->executeOperation()->fetchAll($this->pdoFetchMode, $this->obj);
        }
        else 
        {
            $this->results = $this->executeOperation()->fetchAll($this->pdoFetchMode);
        }
        return $this->results;
    }

    public function getResults(): string
    {
        if ($this->pdoFetchMode == PDO::FETCH_CLASS) 
        {
            $this->results = $this->executeOperation()->fetchAll($this->pdoFetchMode, $this->obj);
        }
        else 
        {
            $this->results = $this->executeOperation()->fetchAll($this->pdoFetchMode);
        }
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