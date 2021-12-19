<?php

require_once "controller/default_cfg.php";
require_once "view/crud.php";
require_once "view/display.php";
require_once "controller/fetch.php";
require_once "controller/db.php";

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
                $ret->GetLatest(
                    $this->getExtraParam("limit") == null 
                    ? CDefaultCfg::getCfgItem("default_pagination") 
                    : $this->getExtraParam("limit")
                );
                echo $ret->getResults();
                break;
            
            case "view":
                $ui = new CCrudUi(new CTableOutput(), new CRowOutput());
                $json_response = file_get_contents(
                    CDefaultCfg::getCfgItem("default_http_root")
                    . "/" . $this->module . "/fetch/" );
                if ($json_response != null && $json_response != "")
                {
                    $ui->render(json_decode($json_response));
                }
                break;
        }


    }
};

?>