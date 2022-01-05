<?php

class CModule implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $module_name;
    /** @var string */
    private $module_table;
    /** @var string */
    private $module_class;
    /** @var string */
    private $http_root;

    public function __construct() 
    {
        if (class_exists("CDefaultCfg"))
        {
            $this->http_root = CDefaultCfg::getCfgItem("default_http_root");
        }
        else 
        {
            $this->http_root = "/";
        }
    }

    // Enable setting it outside of constructor in particular cases 
    // where cfg is not available at construction time, e.g. testing
    public function setHttpRoot(string $root) 
    {
        $this->http_root = $root;
    }

    public function jsonSerialize()
    {
        return array(
            "id" => [
                "header" => "id",
                "data" => $this->id,
                "hide" => true,
                "editable" => false,
            ],
            "module_name"   => [
                "header" => "Module",
                "data" => $this->module_name,
                "edit_data" => $this->module_name,
                "link" => $this->http_root."/".$this->module_name."/view",
            ],
            "module_table"   => [
                "header" => "Module table",
                "data" => $this->module_table,
                "edit_data" => $this->module_table,
                "hide" => true,
            ],
            "module_class"   => [
                "header" => "Module class",
                "data" => $this->module_class,
                "edit_data" => $this->module_class,
                "hide" => true,
            ],
        );
    }

    public function setModuleName(string $t) 
    {
        $this->module_name = $t;
    }

    public function setModuleTable(string $d) 
    {
        $this->module_table = $d;
    }

    public function setModuleClass(string $p) 
    {
        $this->module_class = $p;
    }

    public function getModuleName() : string
    {
        return $this->module_name = $t;
    }

    public function getModuleTable() : string
    {
        return $this->module_table;
    }

    public function getModuleClass() : string
    {
        return $this->module_class;
    }
};

?>