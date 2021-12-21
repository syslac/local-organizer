<?php

require_once "controller/default_cfg.php";
require_once "view/crud.php";
require_once "view/display.php";
require_once "controller/fetch.php";
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

    public function dispatch () 
    {
        switch ($this->action) 
        {
            case "fetch":
                $ret = new CFetcher(CDBConfig::getInstance(), $this->module);
                $col_search = false;
                foreach ($this->extra as $k => $v) 
                {
                    if ($ret->searchByColumn($k, $v) != null) 
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
            
            case "":
            case "view":

                include "view/template/base.php";

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
                $ui = new CCrudUi(new CTableOutput(), new CRowOutput());

                $json_response = file_get_contents("php://input");
                if ($json_response != null && $json_response != "")
                {
                    $decoded = json_decode($json_response);
                    if ($decoded != null)
                    {
                        $ui->render($decoded);
                    }
                }
                break;
        }
    }
};

?>