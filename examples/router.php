<?php

require_once __DIR__ . "/../vendor/autoload.php";


class Test
{
    /**
     * @Route({"url": "/articles", "key": "articles", "priority": 222})
     */
    public function articles()
    {
    }
}

$router = new \HemiFrame\Lib\Router();
$router->scanDirectory(__DIR__);
$router->setRoute([
    "key" => "users",
    "url" => "/users",
    "controller" => "/App/Controllers/Users",
]);
$router->setRoute([
    "key" => "users.actions",
    "url" => "/users/{{userId}}",
    "controller" => "/App/Controllers/Users/Actions",
]);
$router->setRoute([
    "key" => "users.companies",
    "url" => "/users/companies",
    "controller" => "/App/Controllers/Users/Companies",
    "priority" => 2,
]);

$router->setRequestUri("/articles");

var_dump($router->match());
