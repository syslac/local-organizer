<?php

interface ITestable 
{
    public function run() : bool;
    public static function assertTrue(bool $expr) : bool;
    public static function assertEqual($a, $b) : bool;
    public function addMethod(callable $m, string $text);
}

class CBaseTest implements ITestable
{
    private $test_num;
    private $class_name;
    private $methods;
    private $num_failed;
    
    public function __construct(string $name, int $num) 
    {
        $this->test_num = $num;
        $this->class_name = $name;
        $this->num_failed = 0;
        $this->methods = [];
    }
    
    public function addMethod(callable $m, string $text) 
    {
        array_push($this->methods, [$m, $text]);
    }

    public static function assertTrue(bool $expr) : bool
    {
        if ($expr) 
        {
            return (bool) $expr;
        }
        else 
        {
            var_dump($expr);
            return (bool) $expr;
        }
    }

    public static function assertEqual($a, $b) : bool
    {
        if ($a === $b) 
        {
            return true;
        }
        else 
        {
            var_dump($a);
            var_dump($b);
            return false;
        }
    }

    public static function assertDifferent($a, $b) : bool
    {
        if ($a !== $b) 
        {
            return true;
        }
        else 
        {
            var_dump($a);
            var_dump($b);
            return false;
        }
    }


    public static function assertContains(string $a, string $b) : bool
    {
        if (strpos($a, $b) !== false)
        {
            return true;
        }
        else 
        {
            var_dump($a);
            var_dump($b);
            return false;
        }
    }

    public static function assertCountEqual(string $a, string $b, int $c) : bool
    {
        $offset = 0; 
        $found = 0;
        while ($found < $c)
        {
            if (strpos($a, $b, $offset) === false)
            {
                var_dump($a);
                var_dump($b);
                var_dump($c);
                var_dump($found);
                return false;
            }
            $offset = strpos($a, $b, $offset) + 1;
            $found++;
        }
        return true;
    }

    public static function assertContainsInOrder(string $a, string $b, string $c) : bool
    {
        $findB = strpos($a, $b);
        $findC = strpos($a, $c);
        if ($findB !== false && $findC !== false && $findB < $findC) 
        {
            return true;
        }
        else 
        {
            var_dump($a);
            var_dump($b);
            var_dump($c);
            return false;
        }
    }

    public function run() : bool 
    {
        echo $this->class_name."\n";
        $run_methods = 0;

        $fatalCatcher = function () use ($run_methods) 
        {
            $error = error_get_last();
            if ($error['type'] === E_ERROR) 
            {
                echo "FAILED: test execution aborted after $run_methods tests because of fatal error \n";
            }
        };
        register_shutdown_function($fatalCatcher);

        foreach ($this->methods as $m)
        {
            try {
                if ($m[0]()) 
                {
                    echo "OK : ".$m[1]."\n";
                }
                else 
                {
                    echo "NOK: ".$m[1]."\n";
                    $this->num_failed++;
                }
            }
            catch (Exception $e) 
            {
                    echo "NOK: ".$m[1]."\n";
                    $this->num_failed++;
            }
            $run_methods++;
        }
        if ($run_methods != $this->test_num)
        {
            $this->num_failed++;
            echo "NOK: Number of tests \n";
        }
        else 
        {
            echo "OK : Number of tests \n";
        }
        if ($this->num_failed > 0)
        {
            echo "FAILED: $this->num_failed/".($this->test_num + 1)." tests not passing \n";
            return false;
        }
        else 
        {
            echo "PASSED: ".($this->test_num + 1)."/".($this->test_num + 1)." tests passing \n";
            return true;
        }
    }
}

?>