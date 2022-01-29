<?php

require "router/route.php";

if (!isset($_SERVER['PATH_INFO']))
{
    $_SERVER['PATH_INFO'] = '';
}
$route = new CRoute($_SERVER['PATH_INFO']);
$route->dispatch();

?>