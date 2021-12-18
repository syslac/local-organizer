<?php

require "router/route.php";

$route = new CRoute($_SERVER['PATH_INFO']);
$route->dispatch();

?>