<?php

require_once "view/base/view_base.php";
require_once "controller/fetch.php";

class CRowOutput implements IDisplayItem
{
    public function getItemHtml(?object $item): string
    {
        if ($item == null)
        {
            return "";
        }
        $ret_val = "";
        $ret_val .= "<td class=\"edit\">✎</td>";
        $ret_val .= "<td class=\"delete\">❌</td>";
        $ret_val .= "<td class=\"add_tag\">🛈</td>";
        foreach($item as $key => $fld)
        {
            $ret_val .= "<td class=\"";
            if (isset($fld->hide) && $fld->hide) 
            {
                $ret_val .= " hidden";
            }
            if (isset($fld->header))
            {
                switch ($fld->header) 
                {
                    case "id":
                        $ret_val .= " id";
                        break;
                    case "is_done":
                        $ret_val .= " done";
                        break;
                    case "tags":
                        $ret_val .= " mtm";
                        break;
                    default:
                        break;
                }
            }
            $ret_val .= "\">";
            if (isset($fld->link)) 
            {
                $ret_val .= "<a href=\"".htmlspecialchars($fld->link)."\">";
            }
            $ret_val .= htmlspecialchars($fld->data);
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
    public function getItemHtml(?object $item): string
    {
        if ($item === null) 
        {
            return "";
        }
        $cast_item = (array) $item;
        return vsprintf(str_repeat("%s|", sizeof($cast_item)), 
        array_map(function ($a) { return htmlspecialchars($a->data); }, array_values($cast_item)));
    }
}

class CTableOutput implements IDisplaySet
{
    public function getSetHtml(array $set, IDisplayItem $itemDisplayer): string
    {
        $ret_val = "<table class=\"table\">
        <thead class=\"table-dark\">
            <tr>";
        if (is_array($set) && sizeof($set) > 0) 
        {
            $item = array_map(function ($v) { return [$v->header, isset($v->hide) ? $v->hide : false]; }, get_object_vars($set[0]));
            $ret_val .= "<th>&nbsp;</th>";
            $ret_val .= "<th>&nbsp;</th>";
            $ret_val .= "<th>&nbsp;</th>";
            foreach ($item as $header) 
            {
                if (!$header[1]) 
                {
                    $ret_val .= "<th class=\"\">";
                    $ret_val .= htmlspecialchars($header[0]);
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
        return "<div class=\"input_unit\">
        <div class=\"field\">".htmlspecialchars($header)."</div>
        <input type=\"text\" name=\"".htmlspecialchars($name)."\" value=\"".htmlspecialchars($val)."\" />
        </div>";
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
        return "<div class=\"input_unit\">
        <div class=\"field\">".htmlspecialchars($header)."</div>
        <input type=\"date\" name=\"".htmlspecialchars($name)."\" value=\"".htmlspecialchars($val)."\" />
        </div>";
    }
}

class CFormExternalSelect implements IDisplayColumn
{
    private $opt;

    public function __construct(string $ext_module) 
    {
        $ret = new CFetcher(CDBConfig::getInstance(), '', false, false);
        $ret->setConnInfo($ext_module, '');
        $ret->populateQuery();
        $ret->getExtAssoc();
        foreach($ret->getRawResults() as $it)
        {
            $this->opt[$it['id']] = $it['name'];
        }
    }

    public function getColumnEditForm(string $val, string $name, string $header = null) : string
    {
        if ($header == null)
        {
            $header = $name;
        } 
        $ret = "<div class=\"input_unit\">
        <div class=\"field\">".htmlspecialchars($header)."</div>
        <select name=\"".htmlspecialchars($name)."\">";
        foreach ($this->opt as $id => $name) 
        {
            $ret .= "<option value=\"".htmlspecialchars($id)."\"";
            if ($id == $val) 
            {
                $ret .= " selected=\"selected\" ";
            }
            $ret .= ">".htmlspecialchars($name)."</option>";

        }
        $ret .= "</select></div>";
        return $ret;
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
                .htmlspecialchars($module)."/add/\">";
        }
        else 
        {
            $ret_val .= "<form method=\"post\" class=\"object_edit\" action=\""
                .CDefaultCfg::getCfgItem("default_http_root")."/"
                .htmlspecialchars($module)."/edit/id/".htmlspecialchars($id_obj)
                ."\">";
        }

        foreach($item as $field => $metadata)
        {
            if (isset($metadata->editable) && !$metadata->editable) 
            {
                continue;
            }
            $form_item = $this->getInputHandlingClass($metadata->type, $metadata->ext_module);
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

    private function getInputHandlingClass(?string $type, ?string $ext_module = null) : ?IDisplayColumn
    {
        if ($type == null
        || $type == ""
        || $type == "text")
        {
            return new CFormInput();
        }
        else if ($type == "external")
        {
            if (isset($ext_module))
            {
                return new CFormExternalSelect($ext_module);
            }
            else 
            {
                return new CFormInput();
            }
        }
        else if ($type == "date")
        {
            return new CFormDate();
        }
    }
}

?>