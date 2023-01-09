<?php

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Create Container Instance
 */
$container = new \HemiFrame\Lib\DependencyInjection\Container();
$container->setRule(HemiFrame\Interfaces\DependencyInjection\Container::class, [
    "singleton" => true,
    "instance" => function () use ($container) {
        return $container;
    },
]);
$container->setRule(HemiFrame\Lib\Router::class, [
    "singleton" => true,
]);

$router = $container->get(HemiFrame\Lib\Router::class);
