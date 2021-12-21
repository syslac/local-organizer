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
    private $item;
    /** @var string */
    private $link;
    /** @var float */
    private $price;
    /** @var int */
    private $id_priority;
    /** @var DateTime */
    private $deadline;

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
            "id"            => $this->id,
            "id_for_user"   => $this->id_for_user,
            "item"          => $this->item,
            "link"          => $this->link,
            "price"         => $this->price,
            "deadline"      => $this->deadline == null ? null : $this->deadline->format('Y-m-d'),
            "id_priority"   => $this->id_priority,
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
