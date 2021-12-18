<?php

require_once "router/dispatcher.php";
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
            $url = 'todo/view';
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
            $this->extra = join("/", array_slice($parts, 3));
        }
    }

    public function dispatch () 
    {
        if (array_key_exists($this->module, CDispatcher::getModules())) 
        {
            $ret = new CFetcher(CDBConfig::getInstance(), CDispatcher::getModules()[$this->module]);
            $ret->GetLatest(10);

            $ui = new CCrudUi(new CTableOutput(), new CRowOutput());
            $ui->render($ret->getResults());
        }
        else 
        {
            echo "Module $this->module not found! Action requested $this->action";
        }
    }
};

?>