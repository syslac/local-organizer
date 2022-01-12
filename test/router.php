<?php

include_once "test/test_base.php";
include_once "router/route.php";

class CTestRouter extends CBaseTest
{

    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $this->addMethod(function () 
        {
            $empty = new CRoute();
            return CBaseTest::assertEqual($empty->getAction(), "view")
                && CBaseTest::assertEqual($empty->getModule(), "modules")
                && CBaseTest::assertEqual($empty->GetExtraParam("test"), null);
        }, "Default initialization");

        $this->addMethod(function () 
        {
            $empty = new CRoute("/module/action");
            return CBaseTest::assertEqual($empty->getAction(), "action")
                && CBaseTest::assertEqual($empty->getModule(), "module")
                && CBaseTest::assertEqual($empty->GetExtraParam("test"), null);
        }, "Action-only url");

        $this->addMethod(function () 
        {
            $empty = new CRoute("module/action");
            return CBaseTest::assertDifferent($empty->getAction(), "action")
                && CBaseTest::assertDifferent($empty->getModule(), "module");
        }, "Wrong results if path is not /-prefaced");

        $this->addMethod(function () 
        {
            $empty = new CRoute("/module/action/key1/val1/key2/val2");
            return CBaseTest::assertEqual($empty->getAction(), "action")
                && CBaseTest::assertEqual($empty->getModule(), "module")
                && CBaseTest::assertEqual($empty->GetExtraParam("key1"), "val1")
                && CBaseTest::assertEqual($empty->GetExtraParam("key2"), "val2")
                && CBaseTest::assertEqual($empty->GetExtraParam("key3"), null);
        }, "Url with extras");

        $this->addMethod(function () 
        {
            $empty = new CRoute("/module/action/key1");
            return CBaseTest::assertEqual($empty->getAction(), "action")
                && CBaseTest::assertEqual($empty->getModule(), "module")
                && CBaseTest::assertEqual($empty->GetExtraParam("key1"), "");
        }, "Odd-numbered extras defaulting to empty string as value");

        $this->addMethod(function () 
        {
            $empty = new CRoute("/module/action/key1");
            return CBaseTest::assertEqual($empty->getAction(), "action")
                && CBaseTest::assertEqual($empty->getModule(), "module")
                && CBaseTest::assertEqual($empty->GetExtraParam("key1"), "");
        }, "Odd-numbered extras defaulting to empty string as value");

        $this->addMethod(function ()
        {
            $empty = new CRoute();
            ob_start();
            $empty->embed_template("test/router_embed.php", array("test_var" => 5));
            $template = ob_get_clean();
            return CBaseTest::assertEqual($template, "5");
        }, "Templating test with variables");
    }
};

?>