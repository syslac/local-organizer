<?php

include_once "test/view.php";

$view_test = new CTestList("CLiOutput test", 2);
$view_test->run();
$view_test = new CTestTable("CTableRowOutput test", 2);
$view_test->run();

?>