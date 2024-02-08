<?php

namespace Examples;

#[\HemiFrame\Lib\DependencyInjection\Attributes\Singleton]
class TestSingleton
{
    public function __construct()
    {
        echo __CLASS__.PHP_EOL;
    }
}
