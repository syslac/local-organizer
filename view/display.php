<?php

require_once "view/base/view_base.php";

class CRowOutput implements IDisplayItem
{
    public function getItemHtml(object $item): string
    {
        $ret_val = "";
        foreach($item as $key => $fld)
        {
            $ret_val .= "<td class=\"\">";
            if (isset($fld->link)) 
            {
                $ret_val .= "<a href=\"".$fld->link."\">";
            }
            $ret_val .= $fld->data;
            if (isset($fld->link)) 
            {
                $ret_val .= "</a>";
            }
            $ret_val .= "</td>";
        }
        return $ret_val;
    }
}

class CLiOutput implements IDisplayItem
{
    public function getItemHtml(object $item): string
    {
        return vsprintf(str_repeat("%s|", sizeof($item)), 
        array_map(function ($a) { return $a->data; }, array_values($item)));
    }
}

class CTableOutput implements IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $itemDisplayer): string
    {
        $ret_val = "<table class=\"table\">
        <thead class=\"table-dark\">
            <tr>";
        if (sizeof($set > 0)) 
        {
            $item = array_map(function ($v) { return $v->header; }, get_object_vars($set[0]));
            foreach ($item as $header) 
            {
            $ret_val .= "<th class=\"\">";
            $ret_val .= $header;
            $ret_val .= "</th>";
            }
        }
        $ret_val .= "
            </tr>
        </thead>
        <tbody>";
        foreach($set as $item) 
        {
            $ret_val .= "<tr class=\"\">";
            $ret_val .= $itemDisplayer->getItemHtml($item);
            $ret_val .= "</tr>";
        }
        $ret_val .= "
        </tbody>
        </table>";
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