<?php

class CDefaultCfg 
{
    private static $inst = null;
    private $cfg;

    private function __construct() 
    {
        include_once "./cfg.json.php";
        $this->cfg = json_decode($json_cfg, true);
    }

    private function GetDefaultConfig() : array
    {
        return $this->cfg;
    }

    public static function getCfg() : array
    {
        if (self::$inst == null) 
        {
            self::$inst = new CDefaultCfg();
        }
        return self::$inst->GetDefaultConfig();
    }

    public static function getCfgItem(string $key) : ?string
    {
        if (array_key_exists($key, self::getCfg())) 
        {
            return self::getCfg()[$key];
        }
        return null;
    }
};

?>