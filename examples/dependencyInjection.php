<?php

use Examples\Test;
use Examples\TestSingleton;
use Examples\TestSingletonExtend;

require_once __DIR__.'/../vendor/autoload.php';

/**
 * Create Container Instance.
 */
$container = new HemiFrame\Lib\DependencyInjection\Container();
$container->setRule(HemiFrame\Interfaces\DependencyInjection\Container::class, [
    'singleton' => true,
    'instance' => function () use ($container) {
        return $container;
    },
]);
$container->setRule(HemiFrame\Lib\Router::class, [
    'singleton' => true,
]);
$router = $container->get(HemiFrame\Lib\Router::class);

$test = $container->get(Test::class);

$testSingleton = $container->get(TestSingleton::class);
$testSingleton = $container->get(TestSingleton::class);
$testSingleton = $container->get(TestSingleton::class);

$testSingletonExtend = $container->get(TestSingletonExtend::class);
$testSingletonExtend = $container->get(TestSingletonExtend::class);
$testSingletonExtend = $container->get(TestSingletonExtend::class);
