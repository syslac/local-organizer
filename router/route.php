<?php

require_once "controller/default_cfg.php";
require_once "view/crud.php";
require_once "view/display.php";
require_once "controller/fetch.php";
require_once "controller/editor.php";
require_once "controller/adder.php";
require_once "controller/deleter.php";
require_once "controller/db.php";
require_once "view/client_side_request.php";

class CRoute
{
    private $url;
    private $module;
    private $action;
    private $extra;

    public function __construct($url)
    {
        if ($url == null || $url == "") 
        {
            $url = CDefaultCfg::getCfgItem("default_url");
        }
        $this->url = $url;
        $this->update_from_url();
    }

    public function update_from_url() 
    {
        $parts = explode("/", $this->url);
        if (sizeof($parts) > 1) 
        {
            $this->module = $parts[1];
        }
        if (sizeof($parts) > 2) 
        {
            $this->action = $parts[2];
        }
        if (sizeof($parts) > 3) 
        {
            for ($i = 3; $i + 1 < sizeof($parts); $i+= 2) 
            {
                $this->extra[$parts[$i]] = $parts[$i + 1];
            }
        }
    }

    public function getExtraParam (string $param) : ?string
    {
        if (array_key_exists($param, $this->extra)) 
        {
            return $this->extra[$param];
        }
        else 
        {
            return null;
        }
    }

    public function embed_template(string $filename, array $vars) 
    {
        extract($vars);
        include $filename;
    }

    public function dispatch () 
    {
        switch ($this->action) 
        {
            case "fetch":
                $ret = new CFetcher(CDBConfig::getInstance(), $this->module);
                $col_search = false;
                foreach ($this->extra as $k => $v) 
                {
                    if ($ret->searchByColumn($k, $v) !== null) 
                    {
                        $col_search = true;
                    }
                }
                if (!$col_search) 
                {
                    $ret->GetLatest(
                        $this->getExtraParam("limit") == null 
                        ? CDefaultCfg::getCfgItem("default_pagination") 
                        : $this->getExtraParam("limit")
                    );
                }
                echo $ret->getResults();
                break;
            
            case "edit":
                $ret = new CEditor(CDBConfig::getInstance(), $this->module);
                $ret->setData($_POST);
                $ret->setCondition($this->extra);
                $ret->run();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;

            case "add":
                $ret = new CAdder(CDBConfig::getInstance(), $this->module);
                $ret->setData($_POST);
                $ret->run();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;

            case "add_mtm":
                $ret = new CAdder(CDBConfig::getInstance(), $_POST["table"], true);
                unset($_POST["table"]);
                $ret->setData($_POST);
                $ret->run();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;

            case "del_mtm":
                $ret = new CDeleter(CDBConfig::getInstance(), $this->extra["table"], true);
                unset($this->extra["table"]);
                $ret->setExternalCondition($this->extra);
                $ret->run();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;

            case "delete":
                $ret = new CDeleter(CDBConfig::getInstance(), $this->module);
                $ret->setCondition($this->extra);
                $ret->run();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;
            
            case "":
            case "view":

                $this->embed_template("view/template/base.php",
                    array(
                        "module" => $this->module,
                        "root" => CDefaultCfg::getCfgItem("default_http_root"),
                        "extra" => $this->extra,
                        "action" => $this->action,
                        "add_url" => "/view/id/0",
                    ));
                $this->embed_template("view/template/base_js.php",
                    array(
                        "module" => $this->module,
                        "root" => CDefaultCfg::getCfgItem("default_http_root"),
                        "new_tag_form" => (new CFormExternalSelect('lo_tags'))->getColumnEditForm(0, 'id_tag', 'Tag'),
                        "edit_url" => "/view/id/",
                        "delete_url" => "/delete/id/",
                        "filter_tag_url" => "/view/id_tag_mtm/",
                        "add_tag_url" => "/add_mtm/",
                    ));

                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/fetch/";
                // pass on "extra" to the fetch
                if ($this->extra != null) 
                {
                    foreach($this->extra as $key => $val) 
                    {
                        $request_string .= $key . "/" . $val . "/";
                    }
                }
                $post_request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/render/";
                $data_req = new CClientSideRequest('GET', $request_string);
                $data_req->addSuccess($post_request_string, 'main_results_table');
                echo $data_req->run();

                break;
            case "render":
                $ui = new CCrudUi(new CTableOutput(), new CRowOutput(), new CFormOutput());

                $json_response = file_get_contents("php://input");
                if ($json_response != null && $json_response != "")
                {
                    $decoded = json_decode($json_response);
                    if ($decoded != null)
                    {
                        if (!isset($decoded->mode) || $decoded->mode == "view")
                        {
                            $ui->render($decoded->data);
                        }
                        else if (sizeof($decoded->data) == 1)
                        {
                            $ui->renderEdit($decoded->data[0], $decoded->module);
                        }
                    }
                }
                break;
        }
    }
};

?>