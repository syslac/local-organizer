<?php

class CDefaultCfg 
{
    private static $inst = null;
    private $cfg;

    private function __construct() 
    {
        $json_string = file_get_contents("./cfg.json");
        $this->cfg = json_decode($json_string, true);
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