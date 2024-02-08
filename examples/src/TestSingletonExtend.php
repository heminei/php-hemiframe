<?php

namespace Examples;

class TestSingletonExtend extends AbstractSingleton
{
    public function __construct()
    {
        echo __CLASS__.PHP_EOL;
    }
}
