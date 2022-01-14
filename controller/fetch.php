<?php

require_once "controller/db.php";
require_once "models/base/model_base.php";
require_once "models/modules.php";
require_once "models/wishlist.php";
require_once "models/todo.php";
require_once "models/tags.php";
require_once "models/bookmarks.php";

class CFetcher extends CDBOperation
{
    private $results;
    private $foreign;
    private $mtm;
    private $searchById;
    private $pdoFetchMode;
    private $searchColError;

    public function __construct($dbHandle, string $module, ?IDBOp $module_fetcher = null, bool $getForeign = true, bool $getMtm = true) 
    {
        parent::__construct($dbHandle, $module, $module_fetcher);

        if (isset($this->dbo) && isset($this->table))
        {
            if ($getForeign)
            {
                $this->getForeign();
            }
            if ($getMtm && !CDBConfig::doSkipMtm($this->table))
            {
                $this->getManyToMany();
            }
        }
        $this->searchById = false;
        $this->pdoFetchMode = PDO::FETCH_CLASS;
        $this->searchColError = false;
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
            SELECT TABLE_TO_MTM.`COLUMN_NAME` AS MTM_ID, 
            TABLE_TO_MTM.`REFERENCED_COLUMN_NAME` AS MTM_ORIG_ID, 
            MTM_TO_FOREIGN.`TABLE_NAME`, 
            MTM_TO_FOREIGN.`COLUMN_NAME`, 
            MTM_TO_FOREIGN.`REFERENCED_TABLE_NAME`, 
            MTM_TO_FOREIGN.`REFERENCED_COLUMN_NAME` 
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
        if (isset($this->foreign) && is_array($this->foreign))
        {
            foreach ($this->foreign as $join) 
            {
                $q_select .= ", FOREIGN_".$foreign_counter.".name AS ".$join["COLUMN_NAME"]."_ext";
                $foreign_counter++;
            }
        }
        $mtm_counter = 0;
        if (isset($this->mtm) && is_array($this->mtm))
        {
            foreach ($this->mtm as $join) 
            {
                $q_select .= ", GROUP_CONCAT(MTMEND_".$mtm_counter.".name) AS ".$join["COLUMN_NAME"]."_mtm";
                $mtm_counter++;
            }
        }
        $q_select .= " FROM ".$this->table." A "; 
        $foreign_counter = 0;
        $q_join = "";
        if (isset($this->foreign) && is_array($this->foreign))
        {
            foreach ($this->foreign as $join) 
            {
                $q_join .= "LEFT JOIN ".$join["REFERENCED_TABLE_NAME"]." 
                FOREIGN_".$foreign_counter." 
                ON A.".$join["COLUMN_NAME"]." = FOREIGN_".$foreign_counter.".".$join["REFERENCED_COLUMN_NAME"]." ";
                $foreign_counter++;
            }
        }
        $mtm_counter = 0;
        if (isset($this->mtm) && is_array($this->mtm))
        {
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
        }
        $q_group = "";
        if (is_array($this->mtm) && sizeof($this->mtm) > 0) 
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
                    foreach ($this->mtm as $join) 
                    {
                        if ($column === $join["COLUMN_NAME"]."_mtm") 
                        {
                            $found_mtm = true;
                            break;
                        }
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