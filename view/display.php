<?php

require_once "view/base/view_base.php";

class CRowOutput implements IDisplayItem
{
    public function getItemHtml(object $item): string
    {
        $ret_val = "";
        $ret_val .= "<td class=\"edit\">✎</td>";
        foreach($item as $key => $fld)
        {
            $ret_val .= "<td class=\"";
            if (isset($fld->hide) && $fld->hide) 
            {
                $ret_val .= " hidden";
            }
            if (isset($fld->header) && $fld->header == "id") 
            {
                $ret_val .= " id";
            }
            $ret_val .= "\">";
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
            $item = array_map(function ($v) { return [$v->header, $v->hide]; }, get_object_vars($set[0]));
            $ret_val .= "<th>&nbsp;</th>";
            foreach ($item as $header) 
            {
                if (!$header[1]) 
                {
                    $ret_val .= "<th class=\"\">";
                    $ret_val .= $header[0];
                    $ret_val .= "</th>";
                }
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

class CFormInput implements IDisplayColumn
{
    public function getColumnEditForm(string $val, string $name, string $header = null) : string
    {
        if ($header == null)
        {
            $header = $name;
        } 
        return "<div class=\"input_unit\"><div class=\"field\">".$header."</div><input type=\"text\" name=\"".$name."\" value=\"".$val."\" /></div>";
    }
}

class CFormDate implements IDisplayColumn
{
    public function getColumnEditForm(string $val, string $name, string $header = null) : string
    {
        if ($header == null)
        {
            $header = $name;
        } 
        return "<div class=\"input_unit\"><div class=\"field\">".$header."</div><input type=\"date\" name=\"".$name."\" value=\"".$val."\" /></div>";
    }
}

class CFormOutput implements IDisplayForm
{
    public function getObjectEditForm(object $item, string $module) : string
    {
        $id_obj = null;
        foreach($item as $field => $metadata)
        {
            if ($field == "id") 
            {
                $id_obj = $metadata->data;
                break;
            }
        } 
        $ret_val = "";
        if ($id_obj == null) 
        {
            $ret_val .= "<form method=\"post\" class=\"object_edit\" action=\""
                .CDefaultCfg::getCfgItem("default_http_root")."/"
                .$module."/add/\">";
        }
        else 
        {
            $ret_val .= "<form method=\"post\" class=\"object_edit\" action=\""
                .CDefaultCfg::getCfgItem("default_http_root")."/"
                .$module."/edit/id/".$id_obj
                ."\">";
        }

        foreach($item as $field => $metadata)
        {
            if (isset($metadata->editable) && !$metadata->editable) 
            {
                continue;
            }
            $form_item = $this->getInputHandlingClass($metadata->type);
            if ($form_item == null)
            {
                continue; // type handling not implemented yet
            }
            if ($metadata->edit_data == null) 
            {
                $metadata->edit_data = "";
            }
            $ret_val .= $form_item->getColumnEditForm($metadata->edit_data, $field, $metadata->header);
            $ret_val .= "<br />";
        }
        $ret_val .= "<input class=\"enter\" type=\"submit\" value=\"Submit\" />";
        $ret_val .= "</form>";
        return $ret_val;
    }

    private function getInputHandlingClass(?string $type) : ?IDisplayColumn
    {
        if ($type == null
        || $type == ""
        || $type == "text")
        {
            return new CFormInput();
        }
        else if ($type == "external")
        {
            return new CFormInput();
        }
        else if ($type == "date")
        {
            return new CFormDate();
        }
    }
}

?>