<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Application {

    const MODE_DEV = "dev";
    const MODE_STAGE = "stage";
    const MODE_PROD = "prod";

    private $mode = self::MODE_PROD;
    private $class;
    private $rootDir = "";
    private $container;
    private $router;

    public function __construct(Interfaces\DependencyInjection\Container $container, \HemiFrame\Lib\Router $router) {
        $this->container = $container;
        $this->router = $router;
    }

    public function getMode(): string {
        return $this->mode;
    }

    public function setMode(string $mode): self {
        $this->mode = $mode;

        return $this;
    }

    public function getRootDir(): string {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getClass() {
        return $this->class;
    }

    public function showDisplayErrors(bool $bool): self {
        if ($bool === TRUE) {
            ini_set("display_errors", 1);
            error_reporting(E_ALL);
        } else {
            ini_set("display_errors", 0);
            error_reporting(0);
        }

        return $this;
    }

    public function run() {
        $route = $this->router->match();
        $class = $route['class'];
        $method = $route['method'];

        if (!empty($class)) {
            $this->class = $this->container->get($class);
            $this->class->onLoad();
            $this->class->$method();
            $this->class->render();
        }

        return $this->class;
    }

}
