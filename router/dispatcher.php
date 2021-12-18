<?php

include_once "models/todo.php";
include_once "models/wishlist.php";

class CDispatcher 
{
    private $modules;
    private static $disp = null;
    
    private function __construct() 
    {
        $this->modules = array(
            "todo" => new CTodo(),
            "wishlist" => new CWishlist(),
        );
    }

    private function getMods() : array
    {
        return $this->modules;
    }

    public static function getModules() : array
    {
        if (self::$disp == null)
        {
            self::$disp = new CDispatcher();
        }
        return self::$disp->getMods();
    }
};

?>