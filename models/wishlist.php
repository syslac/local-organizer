<?php

class CWishlist implements JsonSerializable
{
    /** @var int */
    private $id;
    /** @var int */
    private $id_for_user;
    /** @var string */
    private $id_for_user_ext;
    /** @var string */
    private $item;
    /** @var string */
    private $link;
    /** @var float */
    private $price;
    /** @var int */
    private $id_priority;
    /** @var string */
    private $id_priority_ext;
    /** @var DateTime */
    private $deadline;
    /** @var DateTime */
    private $bought;
    /** @var string */
    private $id_tag_mtm;
    /** @var bool */
    private $is_done;

    public function __construct() 
    {
        if ($this->deadline != '' && $this->deadline != null) 
        {
            $this->deadline = DateTime::createFromFormat('Y-m-d', $this->deadline);
        }
        if ($this->bought != '' && $this->bought != null) 
        {
            $this->bought = DateTime::createFromFormat('Y-m-d', $this->bought);
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
            "id_for_user"   => [
                "header" => "For",
                "data" => $this->id_for_user_ext,
                "edit_data" => $this->id_for_user,
                "type" => "external",
                "ext_module" => "lo_user",
            ],
            "item"          => [
                "header" => "item",
                "data" => $this->item,
                "edit_data" => $this->item,
            ],
            "link"          => [
                "header" => "link",
                "data" => strlen($this->link) < 30 ? $this->link : substr($this->link, 0, 30)." [...]",
                "edit_data" => $this->link,
                "link" => $this->link,
            ],
            "price"         => [
                "header" => "price",
                "data" => $this->price == null ? null : $this->price."€",
                "edit_data" => $this->price,
            ],
            "deadline"      => [
                "header" => "deadline",
                "data" => $this->deadline == null ? null : $this->deadline->format('Y-m-d'),
                "edit_data" => $this->deadline == null ? null : $this->deadline->format('Y-m-d'),
                "type" => "date",
            ],
            "bought"      => [
                "header" => "bought",
                "data" => $this->bought == null ? null : $this->bought->format('Y-m-d'),
                "edit_data" => $this->bought == null ? null : $this->bought->format('Y-m-d'),
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

    public function setIdForUser(int $i) 
    {
        $this->id_for_user = $i;
    }

    public function setIdPriority(int $i) 
    {
        $this->id_priority = $i;
    }

    public function setItem(string $i) 
    {
        $this->item = $i;
    }

    public function setLink(string $l) 
    {
        $this->link = $l;
    }

    public function setPrice(float $p) 
    {
        $this->price = $p;
    }

    public function setDeadline(DateTime $d) 
    {
        $this->deadline = $d;
    }

};

?>