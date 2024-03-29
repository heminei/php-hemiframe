<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Application
{
    public const MODE_DEV = 'dev';
    public const MODE_STAGE = 'stage';
    public const MODE_PROD = 'prod';

    private $mode = self::MODE_PROD;
    private $rootDir = '';
    /**
     * @var Interfaces\DependencyInjection\Container
     */
    private $container;
    /**
     * @var Lib\Router
     */
    private $router;

    public function __construct(Interfaces\DependencyInjection\Container $container, Lib\Router $router)
    {
        $this->container = $container;
        $this->router = $router;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): self
    {
        $this->mode = $mode;

        return $this;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function showDisplayErrors(bool $bool): self
    {
        if (true === $bool) {
            ini_set('display_errors', '1');
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', '0');
            error_reporting(0);
        }

        return $this;
    }

    public function run(): array
    {
        $route = $this->router->match();

        return $route;
    }

    /**
     * Get the value of container.
     *
     * @return Interfaces\DependencyInjection\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
