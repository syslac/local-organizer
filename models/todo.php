<?php

class CTodo implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $title;
    /** @var DateTime */
    private $due_date;
    /** @var DateTime */
    private $done;
    /** @var int */
    private $id_priority;
    /** @var string */
    private $id_priority_ext;
    /** @var string */
    private $id_tag_mtm;
    /** @var bool */
    private $is_done;

    public function __construct() 
    {
        if ($this->due_date != '' && $this->due_date != null) 
        {
            $this->due_date = DateTime::createFromFormat('Y-m-d', $this->due_date);
        }
        if ($this->done != '' && $this->done != null) 
        {
            $this->done = DateTime::createFromFormat('Y-m-d', $this->done);
            $this->is_done = true;
        }
        else
        {
            $this->is_done = false;
        }
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
            "due_date"      => [
                "header" => "Due date",
                "data" => $this->due_date == null ? null : $this->due_date->format('Y-m-d'),
                "edit_data" => $this->due_date == null ? null : $this->due_date->format('Y-m-d'),
                "type" => "date",
            ],
            "done"          => [
                "header" => "Done",
                "data" => $this->done == null ? null : $this->done->format('Y-m-d'),
                "edit_data" => $this->done == null ? null : $this->done->format('Y-m-d'),
                "type" => "date",
            ],
            "id_priority"   => [
                "header" => "priority",
                "data" => $this->id_priority_ext,
                "edit_data" => $this->id_priority,
                "type" => "external",
                "ext_module" => "lo_priority",
            ],
            "is_done" => [
                "header" => "is_done",
                "data" => $this->is_done,
                "hide" => true,
                "editable" => false,
            ],
            "id_tag_mtm"   => [
                "header" => "tags",
                "data" => $this->id_tag_mtm,
                "editable" => false,
            ],
        );
    }

    static public function getMobileFields() 
    {
        return [
            "id" => "id",
            "text" => "title",
            "extra" => "due_date",
        ];
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
};

?>