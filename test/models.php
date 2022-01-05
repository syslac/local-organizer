<?php

include_once "test/test_base.php";
include_once "models/modules.php";
include_once "models/todo.php";
include_once "models/wishlist.php";

class CTestModels extends CBaseTest
{

    public function __construct(string $name, int $num)
    {
        parent::__construct($name, $num);

        $this->addMethod(function () 
        {
            $test_obj = new CWishlist();
            $decoded = json_decode(json_encode($test_obj));
            return CBaseTest::assertEqual(json_last_error(), JSON_ERROR_NONE);
        }, "[Wishlist] Valid JSON serialization");

        $this->addMethod(function () 
        {
            $test_obj = new CWishlist();
            $serialized_null = json_encode($test_obj);
            $test_obj->setDeadline(new DateTime("2021-01-01"));
            $serialized_date = json_encode($test_obj);
            return CBaseTest::assertContains($serialized_null, "\"header\":\"deadline\",\"data\":null")
            && CBaseTest::assertContains($serialized_date, "\"header\":\"deadline\",\"data\":\"2021-01-01\"");
        }, "[Wishlist] Date serialization, null and otherwise");

        $this->addMethod(function () 
        {
            $test_obj = new CWishlist();
            $serialized_null = json_encode($test_obj, JSON_UNESCAPED_UNICODE);
            $test_obj->setPrice(90.25);
            $serialized_date = json_encode($test_obj, JSON_UNESCAPED_UNICODE);
            return CBaseTest::assertContains($serialized_null, "\"header\":\"price\",\"data\":null")
            && CBaseTest::assertContains($serialized_date, "\"header\":\"price\",\"data\":\"90.25€\",\"edit_data\":90.25");
        }, "[Wishlist] Price serialization, handling value format, including edit data");

        $this->addMethod(function () 
        {
            $test_obj = new CTodo();
            $decoded = json_decode(json_encode($test_obj));
            return CBaseTest::assertEqual(json_last_error(), JSON_ERROR_NONE);
        }, "[Todo] Valid JSON serialization");

        $this->addMethod(function () 
        {
            $test_obj = new CTodo();
            $serialized_null = json_encode($test_obj);
            $test_obj->setDueDate(new DateTime("2021-01-01"));
            $serialized_date = json_encode($test_obj);
            return CBaseTest::assertContains($serialized_null, "\"header\":\"Due date\",\"data\":null")
            && CBaseTest::assertContains($serialized_date, "\"header\":\"Due date\",\"data\":\"2021-01-01\"");
        }, "[Todo] Date serialization, null and otherwise");

        $this->addMethod(function () 
        {
            $test_obj = new CModule();
            $decoded = json_decode(json_encode($test_obj));
            return CBaseTest::assertEqual(json_last_error(), JSON_ERROR_NONE);
        }, "[Module] Valid JSON serialization");

        $this->addMethod(function () 
        {
            $test_obj = new CModule();
            $test_obj->setHttpRoot("http://fakewebsite.cc");
            $serialized = json_encode($test_obj);
            return CBaseTest::assertContains($serialized, "fakewebsite.cc");
        }, "[Module] Module link in JSON serialization");
    }
}