<?php

class CDBConfig 
{
    private const user = 'root';
    private const pass = 'SfNTgl8HR26K0Gsf';
    private const db = 'local_organizer';
    private $dbo;
    private static $dbObject = null;

    private function __construct() 
    {
        $dsn = "mysql:host=127.0.0.1;dbname=".self::db.";charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_CLASS,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $this->dbo = new PDO($dsn, self::user, self::pass, $options);
        } catch (\PDOException $e) {
            echo "connection failed";
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    private function getHandle() 
    {
        return $this->dbo;
    }

    public function getInstance() 
    {
        if (self::$dbObject == null)
        {
            self::$dbObject = new CDBConfig();
        }
 
        return self::$dbObject->getHandle();
    }
}

?>