<?php

namespace Tests\Unit\Lib\Routing\Attributes;

class RouteTest extends \PHPUnit\Framework\TestCase
{
    public function testRoute(): void
    {
        $route = new \HemiFrame\Lib\Routing\Attributes\Route('/test', 'test');
        $this->assertInstanceOf(\HemiFrame\Lib\Routing\Attributes\Route::class, $route);

        $this->assertEquals('/test', $route->url);
        $this->assertEquals('test', $route->key);
        $this->assertEquals(1, $route->priority);
        $this->assertNull($route->host);
        $this->assertNull($route->lang);
    }

    public function testRouteWithOptionalArguments(): void
    {
        $route = new \HemiFrame\Lib\Routing\Attributes\Route('/test', 'test', 10, 'api.', 'en');

        $this->assertEquals('/test', $route->url);
        $this->assertEquals('test', $route->key);
        $this->assertEquals(10, $route->priority);
        $this->assertEquals('api.', $route->host);
        $this->assertEquals('en', $route->lang);
    }
}
