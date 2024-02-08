<?php

namespace Tests\Unit\Lib\Routing\Attributes;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testRoute()
    {
        $route = new \HemiFrame\Lib\Routing\Attributes\Route('/test', 'test');
        $this->assertInstanceOf(\HemiFrame\Lib\Routing\Attributes\Route::class, $route);

        $this->assertEquals('/test', $route->path);
        $this->assertEquals('test', $route->key);
    }
}
