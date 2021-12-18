<?php

require_once "view/base/view_base.php";

class CRowOutput implements IDisplayItem
{
    public function getItemHtml(IDisplayable $item): string
    {
        $ret_val = "";
        foreach($item->getDisplayableFields() as $fld)
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
    public function getItemHtml(IDisplayable $item): string
    {
        return vsprintf($item->getDisplayableFormat(), $item->getDisplayableFields());
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