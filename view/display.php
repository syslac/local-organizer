<?php

require_once "view/base/view_base.php";

class CRowOutput implements IDisplayItem
{
    public function getItemHtml(object $item): string
    {
        $ret_val = "";
        foreach($item as $key => $fld)
        {
            $ret_val .= "<td>";
            $ret_val .= $fld;
            $ret_val .= "</td>";
        }
        return $ret_val;
    }
}

class CLiOutput implements IDisplayItem
{
    public function getItemHtml(object $item): string
    {
        return vsprintf(str_repeat("%s|", sizeof($item)), array_values($item));
    }
}

class CTableOutput implements IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $itemDisplayer): string
    {
        $ret_val = "<table>";
        foreach($set as $item) 
        {
            $ret_val .= "<tr>";
            $ret_val .= $itemDisplayer->getItemHtml($item);
            $ret_val .= "</tr>";
        }
        $ret_val .= "</table>";
        return $ret_val;
    }
};

class CListOutput implements IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $itemDisplayer): string
    {
        $ret_val = "<ul>";
        foreach($set as $item) 
        {
            $ret_val .= "<li>";
            $ret_val .= $itemDisplayer->getItemHtml($item);
            $ret_val .= "</li>";
        }
        $ret_val .= "</ul>";
        return $ret_val;
    }
}

?>