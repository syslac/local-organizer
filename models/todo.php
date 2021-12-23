<?php

require_once "models/base/model_base.php";
require_once "view/base/view_base.php";

class CTodo implements ITaggable, IDisplayable, JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var DateTime */
    private $due_date;
    /** @var int */
    private $id_priority;
    /** @var string */
    private $id_priority_ext;

    public function __construct() 
    {
        if ($this->due_date != '' && $this->due_date != null) 
        {
            $this->due_date = DateTime::createFromFormat('Y-m-d', $this->due_date);
        }
    }

    public function jsonSerialize()
    {
        return array(
            "title"         => [
                "header" => "Title",
                "data" => $this->title,
            ],
            "due_date"      => [
                "header" => "Due date",
                "data" => $this->due_date == null ? null : $this->due_date->format('Y-m-d'),
            ],
            "id_priority"   => [
                "header" => "priority",
                "data" => $this->id_priority_ext,
            ],
        );
    }

    public function setTitle(string $t) 
    {
        $this->title = $t;
    }

    public function setDueDate(DateTime $d) 
    {
        $this->due_date = $d;
    }

    public function setIdPriority(int $p) 
    {
        $this->id_priority = $p;
    }

    public function addTags(array $tags) 
    {
        foreach($tags as $tag) 
        {
            echo $tag;
        }
    }

    public function getTags() : array
    {
        return [];
    }

    public function getDisplayableFormat() : string 
    {
        return "[%d] %s (%s)"; 
    }

    public function getDisplayableFields() : array
    {
        return [
            $this->id_priority,
            $this->title,
            $this->due_date == null ? '' : $this->due_date->format('Y-m-d'),
        ];
    }
};

?>