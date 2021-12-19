<?php

require_once "models/base/model_base.php";
require_once "view/base/view_base.php";

class CModule implements IDisplayable, JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $module_name;
    /** @var string */
    private $module_table;
    /** @var string */
    private $module_class;

    public function __construct() 
    {
    }

    public function jsonSerialize()
    {
        return array(
            "id"            => $this->id,
            "module_name"   => $this->module_name,
            "module_table"  => $this->module_table,
            "module_class"  => $this->module_class,
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

    public function getDisplayableFormat() : string 
    {
        return "%s"; 
    }

    public function getDisplayableFields() : array
    {
        return [
            $this->module_name
        ];
    }
};

?>