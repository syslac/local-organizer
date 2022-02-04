<?php

class CBookmark implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $name;
    /** @var string */
    private $link;

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
                "header" => "Name",
                "data" => $this->name,
                "edit_data" => $this->name,
            ],
            "link"   => [
                "header" => "Link",
                "data" => strlen($this->link) < 30 ? $this->link : substr($this->link, 0, 30)." [...]",
                "edit_data" => $this->link,
                "link" => $this->link,
            ],
        );
    }

    public function setName(string $t) 
    {
        $this->name = $t;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function setLink(string $t) 
    {
        $this->link = $t;
    }

    public function getLink() : string
    {
        return $this->link;
    }
};

?>