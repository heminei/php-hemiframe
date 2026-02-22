<?php

namespace Tests\Unit\Lib;

class RouterTest extends \PHPUnit\Framework\TestCase
{
    private array $serverBackup = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->serverBackup = $_SERVER;
        $_SERVER['SCRIPT_NAME'] = '';
        $_SERVER['HTTP_HOST'] = 'example.com';
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->serverBackup;

        parent::tearDown();
    }

    public function testSetRouteRequiresKey(): void
    {
        $router = new \HemiFrame\Lib\Router();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Enter key');

        $router->setRoute([
            'url' => '/test',
            'controller' => 'Controller',
        ]);
    }

    public function testSetPatternThrowsForUnknownPatternKey(): void
    {
        $router = new \HemiFrame\Lib\Router();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid pattern key: unknown');

        $router->setPattern('unknown', '.*');
    }

    public function testMatchReturnsConfiguredRouteWithVarsAndMethodReplacement(): void
    {
        $router = new \HemiFrame\Lib\Router();
        $router->setHost('example.com');
        $router->setRoute([
            'key' => 'user.show',
            'url' => '/user/{{id|number}}',
            'controller' => 'App\\Controllers\\UserController',
            'method' => 'show{{id|number}}',
        ]);

        $router->setRequestUri('/user/42');
        $route = $router->match();

        $this->assertSame('user.show', $route['key']);
        $this->assertSame('App\\Controllers\\UserController', $route['class']);
        $this->assertSame('show42', $route['method']);
        $this->assertSame(['id' => '42'], $route['vars']);
        $this->assertSame(1, $route['priority']);
    }

    public function testMatchFallsBackToDefaultClassAndMethodWhenNoRouteMatched(): void
    {
        $router = new \HemiFrame\Lib\Router();
        $router->setDefaultClass('App\\Controllers\\DefaultController');
        $router->setDefaultMethod('index');
        $router->setRequestUri('/missing');

        $route = $router->match();

        $this->assertNull($route['key']);
        $this->assertSame('App\\Controllers\\DefaultController', $route['class']);
        $this->assertSame('index', $route['method']);
        $this->assertSame([], $route['vars']);
    }

    public function testApplyUrlPrioritiesPrefersHigherPriorityRoute(): void
    {
        $router = new \HemiFrame\Lib\Router();
        $router->setHost('example.com');

        $router->setRoute([
            'key' => 'generic',
            'url' => '/product/{{value}}',
            'controller' => 'GenericController',
            'method' => 'generic',
            'priority' => 1,
        ]);
        $router->setRoute([
            'key' => 'numeric',
            'url' => '/product/{{value|number}}',
            'controller' => 'NumericController',
            'method' => 'numeric',
            'priority' => 10,
        ]);

        $router->setRequestUri('/product/99');
        $route = $router->match();

        $this->assertSame('numeric', $route['key']);
        $this->assertSame('NumericController', $route['class']);
        $this->assertSame('numeric', $route['method']);
    }

    public function testGetRouteBuildsLangAndVarUrl(): void
    {
        $router = new \HemiFrame\Lib\Router();
        $router->setLang('en');
        $router->setBasePath('/base');
        $router->setRequestUri('/any');
        $router->setRoute([
            'key' => 'profile',
            'lang' => 'en',
            'url' => '/users/{{id|number}}',
            'controller' => 'ProfileController',
        ]);

        $url = $router->getRoute([
            'key' => 'profile',
            'vars' => [
                'id' => 123,
            ],
        ]);

        $this->assertSame('/base/en/users/123', $url);
    }
}
