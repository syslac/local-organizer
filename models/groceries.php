<?php

class CGroceries implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var string */
    private $item;
    /** @var num */
    private $qty;
    /** @var string */
    private $unit;
    /** @var DateTime */
    private $deadline;
    /** @var int */
    private $id_priority;
    /** @var string */
    private $id_priority_ext;
    /** @var string */
    private $id_tag_mtm;

    public function __construct() 
    {
        if ($this->deadline != '' && $this->deadline != null) 
        {
            $this->deadline = DateTime::createFromFormat('Y-m-d', $this->deadline);
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
            "item"         => [
                "header" => "Item",
                "data" => $this->item,
                "edit_data" => $this->item,
            ],
            "qty"         => [
                "header" => "Quantity",
                "data" => $this->qty,
                "edit_data" => $this->qty,
            ],
            "unit"         => [
                "header" => "Unit",
                "data" => $this->unit,
                "edit_data" => $this->unit,
            ],
            "deadline"      => [
                "header" => "Deadline",
                "data" => $this->deadline == null ? null : $this->deadline->format('Y-m-d'),
                "edit_data" => $this->deadline == null ? null : $this->deadline->format('Y-m-d'),
                "type" => "date",
            ],
            "id_priority"   => [
                "header" => "priority",
                "data" => $this->id_priority_ext,
                "edit_data" => $this->id_priority,
                "type" => "external",
                "ext_module" => "lo_priority",
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
            "text" => "item",
            "extra" => "qty",
        ];
    }

};

?>