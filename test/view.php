<?php

include_once "test/test_base.php";
include_once "view/display.php";

class CTestList extends CBaseTest
{
    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $test_data = new stdClass();
        $test_data->id = (object) [
            "data" => 2142,
            "header" => "id",
        ];
        $test_data->name = (object) [
            "data" => "test",
            "header" => "name",
        ];

        $test_full_list = [
            $test_data,
            $test_data,
            $test_data,
        ];

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CLiOutput();
            return CBaseTest::assertEqual($test_obj->getItemHtml(null), "");
        }, "[Object] Empty object print");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CLiOutput();
            return 
                CBaseTest::assertDifferent($test_obj->getItemHtml($test_data), "")
                && CBaseTest::assertContains($test_obj->getItemHtml($test_data), "test");
        }, "[Object] Sample object print");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CLiOutput();
            return 
                CBaseTest::assertDifferent($test_obj->getItemHtml($test_data), "")
                && CBaseTest::assertContainsInOrder($test_obj->getItemHtml($test_data), "2142", "test");
        }, "[Object] Sample object field order");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_li = new CLiOutput();
            $test_obj = new CListOutput();

            return CBaseTest::assertContainsInOrder($test_obj->getSetHtml([], $test_li), "<ul>", "</ul>");
        }, "[Set] Empty list output");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_li = new CLiOutput();
            $test_obj = new CListOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_li), "<li>", 3);
        }, "[Set] Count <li>");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_li = new CLiOutput();
            $test_obj = new CListOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_li), "test", 3);
        }, "[Set] Count content");
    }
};

class CTestTable extends CBaseTest
{
    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $test_data = new stdClass();
        $test_data->id = (object) [
            "data" => 2142,
            "header" => "id",
        ];
        $test_data->name = (object) [
            "data" => "test",
            "header" => "name",
        ];
        $test_data->link = (object) [
            "data" => "cool_link",
            "header" => "link",
            "link" => "http://localhost",
        ];

        $test_full_list = [
            $test_data,
            $test_data,
            $test_data,
        ];
        $test_full_list[2]->name->data = "last_test";
        unset($test_full_list[2]->name->link);

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertEqual($test_obj->getItemHtml(null), "");
        }, "[Object] Empty object print");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertContains($test_obj->getItemHtml($test_data), "test")
            && CBaseTest::assertContains($test_obj->getItemHtml($test_data), "2142");
        }, "[Object] Sample object print");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertContains($test_obj->getItemHtml($test_data), "delete")
            && CBaseTest::assertContains($test_obj->getItemHtml($test_data), "edit");
        }, "[Object] Meta operations cells");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertContains($test_obj->getItemHtml($test_data), 
            "<a href=\"http://localhost\">cool_link");
        }, "[Object] Object with link");

        $this->addMethod(function () use ($test_data)
        {
            $test_obj = new CRowOutput();
            return CBaseTest::assertCountEqual($test_obj->getItemHtml($test_data), "<td", 5);
        }, "[Object] Count <td>s");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_tr = new CRowOutput();
            $test_obj = new CTableOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_tr), "<tr", 3);
        }, "[Set] Count <tr>s");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_tr = new CRowOutput();
            $test_obj = new CTableOutput();

            return CBaseTest::assertContains($test_obj->getSetHtml($test_full_list, $test_tr), "thead");
        }, "[Set] Sets <thead>");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_tr = new CRowOutput();
            $test_obj = new CTableOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_tr), "2142", 3);
        }, "[Set] Count content");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_tr = new CRowOutput();
            $test_obj = new CTableOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_tr), "<a", 2);
        }, "[Set] Count links");

        $this->addMethod(function () use ($test_full_list)
        {
            $test_tr = new CRowOutput();
            $test_obj = new CTableOutput();

            return CBaseTest::assertCountEqual($test_obj->getSetHtml($test_full_list, $test_tr), "last_test", 1);
        }, "[Set] Diff. content");
    }
};

?>