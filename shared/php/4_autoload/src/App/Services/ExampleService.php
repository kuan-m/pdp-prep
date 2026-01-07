<?php

namespace App\Services;

class ExampleService
{
    public function sayHello(string $name): string
    {
        return "Hello, {$name} from " . __CLASS__;
    }
}


