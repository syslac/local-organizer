<?php

require_once "models/base/model_base.php";
require_once "view/base/view_base.php";

class CWishlist implements ITaggable, IDisplayable, JsonSerializable
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
                "data" => $this->link,
                "edit_data" => $this->link,
                "link" => $this->link,
            ],
            "price"         => [
                "header" => "price",
                "data" => $this->price."€",
                "edit_data" => $this->price,
            ],
            "deadline"      => [
                "header" => "deadline",
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
        return "%s (for %d) [%d]"; 
    }

    public function getDisplayableFields() : array
    {
        return [
            $this->item,
            $this->id_for_user,
            $this->id_priority,
        ];
    }
};

?>
