<?php

require_once "controller/default_cfg.php";
require_once "controller/relation.php";
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

    public function __construct($url = "")
    {
        if ($url == null || $url == "") 
        {
            $url = CDefaultCfg::getCfgItem("default_url");
        }
        $this->url = $url;
        $this->update_from_url();
    }

    public function getAction() 
    {
        return $this->action;
    }

    public function getModule() 
    {
        return $this->module;
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
            for ($i = 3; $i < sizeof($parts); $i+= 2) 
            {
                if ($i + 1 < sizeof($parts)) 
                {
                    $this->extra[$parts[$i]] = $parts[$i + 1];
                }
                else 
                {
                    $this->extra[$parts[$i]] = "";
                }
            }
        }
        unset($this->extra[""]);
    }

    public function getExtraParam (string $param) : ?string
    {
        if (
            $this->extra === null 
            || !array_key_exists($param, $this->extra)
            ) 
        {
            return null;
        }
        else 
        {
            return $this->extra[$param];
        }
    }

    public function embed_template(string $filename, array $vars) 
    {
        extract($vars);
        include $filename;
    }

    public function rerouteToView() 
    {
        $request_string = CDefaultCfg::getCfgItem("default_http_root")
            . "/" . $this->module . "/view/";
        header("Location: ".$request_string);
    }

    public function dispatch () 
    {
        $module_fetcher = new CFetcher(CDBConfig::getInstance(), "modules", null, new CNullRelationFinder());
        switch ($this->action) 
        {
            case "fetch":
                $ret = new CFetcher(CDBConfig::getInstance(), $this->module, $module_fetcher, new CMySQLRelationFinder());
                $col_search = false;
                foreach ($this->extra as $k => $v) 
                {
                    $ret->setSearchColumn();
                    $ret->setOperationParams([$k, $v]);
                    if (!$ret->getResetSearchColError()) 
                    {
                        $col_search = true;
                    }
                }
                if (!$col_search) 
                {
                    $ret->setGetLatest();
                    $ret->setOperationParams(
                        $this->getExtraParam("limit") == null 
                        ? [CDefaultCfg::getCfgItem("default_pagination")]
                        : [$this->getExtraParam("limit")]
                    );
                }
                echo $ret->getResults();
                break;
            
            case "edit":
                $ret = new CEditor(CDBConfig::getInstance(), $this->module, $module_fetcher);
                $ret->setOperationParams([
                    "data" => $_POST,
                    "condition" => $this->extra,
                ]);
                $ret->executeOperation();
                $request_string = CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/view/";
                header("Location: ".$request_string);
                break;

            case "add":
                $ret = new CAdder(CDBConfig::getInstance(), $this->module, $module_fetcher);
                $ret->setOperationParams($_POST);
                $ret->executeOperation();
                $this->rerouteToView();
                break;

            case "add_mtm":
                $ret = new CAdder(CDBConfig::getInstance(), $_POST["table"], $module_fetcher, true);
                unset($_POST["table"]);
                $ret->setOperationParams($_POST);
                $ret->executeOperation();
                $this->rerouteToView();
                break;

            case "del_mtm":
                $ret = new CDeleter(CDBConfig::getInstance(), $this->extra["table"], $module_fetcher, true);
                unset($this->extra["table"]);
                $ret->setOperationParams($this->extra);
                $ret->executeOperation();
                $this->rerouteToView();
                break;

            case "delete":
                $ret = new CDeleter(CDBConfig::getInstance(), $this->module, $module_fetcher);
                $ret->setOperationParams($this->extra);
                $ret->executeOperation();
                $this->rerouteToView();
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