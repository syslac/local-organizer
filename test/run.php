<?php

include_once "test/view.php";
include_once "test/models.php";

$view_test = new CTestList("=== \n List Output test \n===", 6);
$view_test->run();
$view_test = new CTestTable("=== \n Table Output test \n===", 10);
$view_test->run();
$model_test = new CTestModels("=== \n Model test \n===", 7);
$model_test->run();

?>