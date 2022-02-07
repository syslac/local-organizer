<?php

class CTag implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;

    public function __construct() 
    {
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
            "name"   => [
                "header" => "Tag",
                "data" => $this->name,
                "edit_data" => $this->name,
            ],
        );
    }

    static public function getMobileFields() 
    {
        return [
            "id" => "id",
            "text" => "name",
            "extra" => null,
        ];
    }

    public function setName(string $t) 
    {
        $this->name = $t;
    }

    public function getName() : string
    {
        return $this->name;
    }
};

?>