<?php

include_once "test/test_base.php";
include_once "view/display.php";

class CTestList extends CBaseTest
{
    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $this->addMethod(function () 
        {
            $test_obj = new CLiOutput();
            return CBaseTest::assertEqual($test_obj->getItemHtml(null), "");
        }, "Empty object print");

        $this->addMethod(function () 
        {
            $test_obj = new CLiOutput();
            $test_data = new stdClass();
            $test_data->data = "test";
            return !CBaseTest::assertEqual($test_obj->getItemHtml($test_data), "");
        }, "Sample object print");
    }
};

class CTestTable extends CBaseTest
{
    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $this->addMethod(function () 
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertEqual($test_obj->getItemHtml(null), "");
        }, "Empty object print");

        $this->addMethod(function () 
        {
            $test_obj = new CLiOutput();
            $test_data = new stdClass();
            $test_data->data = "test";
            return !CBaseTest::assertEqual($test_obj->getItemHtml($test_data), "");
        }, "Sample object print");
    }
};

?>