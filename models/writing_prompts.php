<?php

class CWritingPrompts implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var string */
    private $excerpt;
    /** @var string */
    private $id_tag_mtm;

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
            "title"         => [
                "header" => "Title",
                "data" => $this->title,
                "edit_data" => $this->title,
            ],
            "excerpt"         => [
                "header" => "Excerpt",
                "data" => $this->excerpt,
                "edit_data" => $this->excerpt,
            ],
            "id_tag_mtm"   => [
                "header" => "tags",
                "data" => $this->id_tag_mtm,
                "editable" => false,
            ]
        );
    }

    static public function getMobileFields() 
    {
        return [
            "id" => "id",
            "text" => "title",
            "extra" => "",
        ];
    }

};

?>
