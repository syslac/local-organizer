<?php

require_once "models/base/model_base.php";

class CFetcher 
{
    private $results;
    private $table;
    private $obj;
    private $dbo;

    public function __construct($dbHandle, IFetchable $object) 
    {
        $this->dbo = $dbHandle;
        $this->table = $object->getTableName();
        $this->obj = $object->getClassName();
    }

    public function getLatest(int $limit) 
    {
        $stmt = $this->dbo->prepare("SELECT * FROM ".$this->table." ORDER BY id DESC LIMIT ?");
        $stmt->execute([$limit]);
        $this->results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->obj);
    }

    public function getResults(): array
    {
        return $this->results;
    }
    // here I probably want to output this as JSON, to keep separation
    // so change displayer classes to get json instead
}

?>