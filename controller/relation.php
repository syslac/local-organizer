<?php

class CForeign 
{
    // every member public, basically just a struct containing fixed fields
    public $is_mtm;
    public $local_col;
    public $foreign_col;
    public $foreign_table;
    public $mtm_middle_table;
    public $mtm_middle_col_from;
    public $mtm_middle_col_to;

    public function __construct (
        $mtm = null, 
        $l_col = null, 
        $f_col = null, 
        $f_table = null, 
        $m_m_t = null, 
        $m_c_f = null, 
        $m_c_t = null
        )
    {
        $this->is_mtm = $mtm;
        $this->local_col = $l_col;
        $this->foreign_col = $f_col;
        $this->foreign_table = $f_table;
        $this->mtm_middle_table = $m_m_t;
        $this->mtm_middle_col_from = $m_c_f;
        $this->mtm_middle_col_to = $m_c_t;
    }
};

interface IRelationFinder 
{
    public function init(string $table, $dbo, bool $skipMtm);
    public function getNumDirectRelations() : int;
    public function getNumMtmRelations() : int;
    public function getNextDirectRelation() : ?CForeign;
    public function getNextMtmRelation() : ?CForeign;
    public function resetIterators();
};

class CNullRelationFinder implements IRelationFinder 
{
    public function __construct () 
    {
        return;
    }

    public function init(string $table, $dbo, bool $skipMtm) 
    {
        return;
    }

    public function getNumDirectRelations () : int 
    {
        return 0;
    }

    public function getNumMtmRelations () : int 
    {
        return 0;
    }

    public function getNextDirectRelation () : ?CForeign 
    {
        return null;
    }

    public function getNextMtmRelation () : ?CForeign 
    {
        return null;
    }

    public function resetIterators () {}
};

class CGenericRelationFinder implements IRelationFinder
{
    protected $model_relations;
    private $consumed_direct;
    private $consumed_mtm;

    public function __construct() 
    {
        // Implement $model_relations construction in sub classes
        $this->consumed_direct = -1;
        $this->consumed_mtm = -1;
        $this->model_relations = array();
    }

    public function init(string $table, $dbo, bool $skipMtm) 
    {
        return;
    }

    public function getNumDirectRelations() : int
    {
        $count = 0;
        if (!is_array($this->model_relations))
        {
            return $count;
        }
        foreach ($this->model_relations as $rel) 
        {
            if (!$rel->is_mtm)
            {
                $count++;
            }
        }
        return $count;
    }

    public function getNumMtmRelations() : int
    {
        $count = 0;
        if (!is_array($this->model_relations))
        {
            return $count;
        }
        foreach ($this->model_relations as $rel) 
        {
            if ($rel->is_mtm)
            {
                $count++;
            }
        }
        return $count;
    }

    public function getNextDirectRelation () : ?CForeign 
    {
        $this->consumed_direct++;
        while (is_array($this->model_relations) && $this->consumed_direct < count($this->model_relations))
        {
            $test = $this->model_relations[$this->consumed_direct];
            if (!$test->is_mtm) 
            {
                return $test;
            }
            $this->consumed_direct++;
        }
        $this->consumed_direct = is_array($this->model_relations) ? count($this->model_relations) : 0;
        return null;
    }

    public function getNextMtmRelation () : ?CForeign 
    {
        $this->consumed_mtm++;
        while (is_array($this->model_relations) && $this->consumed_mtm < count($this->model_relations))
        {
            $test = $this->model_relations[$this->consumed_mtm];
            if ($test->is_mtm) 
            {
                return $test;
            }
            $this->consumed_mtm++;
        }
        $this->consumed_mtm = is_array($this->model_relations) ? count($this->model_relations) : 0;
        return null;
    }

    public function resetIterators () 
    {
        $this->consumed_direct = -1;
        $this->consumed_mtm = -1;
    }

}

class CHardcodedRelationFinder extends CGenericRelationFinder 
{
    public function __construct(?string $table = null) 
    {
        parent::__construct();
        if (isset($table))
        {
            $this->init($table);
        }
    }

    public function init(string $table, $dbo = null, bool $skipMtm = false) 
    {
        switch($table) 
        {
            case "lo_wishlist":
                $this->model_relations = array(
                new CForeign(false, "id_user", "id", "lo_user"),
                new CForeign(false, "id_priority", "id", "lo_priority"),
                new CForeign(true, "id", "id", "lo_tags", "lo_wishlist_tags", "id_wishlist", "id_tag"),
                );
                break;
            case "lo_todo":
                $this->model_relations = array(
                new CForeign(false, "id_priority", "id", "lo_priority"),
                new CForeign(true, "id", "id", "lo_tags", "lo_todo_tags", "id_todo", "id_tag"),
                );
                break;
            case "lo_bookmarks":
            case "lo_tags":
            case "lo_modules":
            default:
                $this->model_relations = array();
                break;
        }
    }
};

require_once "controller/default_cfg.php";

class CMySQLRelationFinder extends CGenericRelationFinder
{
    public function __construct(?string $table = null)
    {
        parent::__construct();
        if (isset($table)) 
        {
            init($table);
        }
    }

    public function init(string $table, $dbo = null, bool $skipMtm = false) 
    {
        if ($dbo === null) 
        {
            return;
        }
        $stmt = $dbo->prepare("
            SELECT `COLUMN_NAME`, `REFERENCED_TABLE_NAME`, `REFERENCED_COLUMN_NAME` 
            FROM `information_schema`.`KEY_COLUMN_USAGE` 
            WHERE `TABLE_SCHEMA` = ? 
            AND `TABLE_NAME` = ? 
            AND `REFERENCED_TABLE_SCHEMA` IS NOT NULL 
            AND `REFERENCED_TABLE_NAME` IS NOT NULL 
            AND `REFERENCED_COLUMN_NAME` IS NOT NULL
        ");
        $stmt->execute([CDefaultCfg::getCfgItem("db_name"), $table]);
        $foreign = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach($foreign as $f) 
        {
            array_push($this->model_relations, new CForeign(
                false, $f["COLUMN_NAME"], $f["REFERENCED_COLUMN_NAME"], $f["REFERENCED_TABLE_NAME"])
            );
        }

        if (!$skipMtm)
        {
            $stmt = $dbo->prepare("
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
            $stmt->execute([CDefaultCfg::getCfgItem("db_name"), CDefaultCfg::getCfgItem("db_name"), $table]);
            $mtm = $stmt->fetchAll(PDO::FETCH_ASSOC);
            foreach($mtm as $m) 
            {
                array_push($this->model_relations, new CForeign(
                    true, $m["MTM_ORIG_ID"], $m["REFERENCED_COLUMN_NAME"], $m["REFERENCED_TABLE_NAME"], $m["TABLE_NAME"], $m["MTM_ID"], $m["COLUMN_NAME"])
                );
            }
        }
    }

};

?>
