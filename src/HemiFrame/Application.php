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
	private $rootDir = null;
	private $container = null;

	public function __construct(Interfaces\DependencyInjection\Container $container) {
		$this->container = $container;
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
		$sapiType = php_sapi_name();
		if ($sapiType != "cli") {
			$uri = $_SERVER['REQUEST_URI'];
		} else {
			if (isset($_SERVER['argv'][1])) {
				$uri = $_SERVER['argv'][1];
			} else {
				$uri = "/";
			}
			$getQuery = parse_url($uri, PHP_URL_QUERY);
			$getVars = array();
			parse_str($getQuery, $getVars);
			if (!empty($getVars)) {
				foreach ($getVars as $key => $value) {
					$_GET[$key] = $value;
					$_REQUEST[$key] = $value;
				}
			}
		}
		/* @var $urlRouting \HemiFrame\Lib\UrlRouting */
		$urlRouting = $this->container->get("Config\UrlManager")->getUrlRouting();
		$urlRouting->setRequestUri($uri);
		$result = $urlRouting->match();
		$class = $result['class'];
		$method = $result['method'];
		if ($class === null) {
			$class = $this->container->get("Config\Config")->getDefaultController();
		}
		if ($method === null) {
			$method = $this->container->get("Config\Config")->getDefaultControllerMethod();
		}

		$this->class = $this->container->get($class);
		$this->class->onLoad();
		$this->class->$method();
		$this->class->render();

		return $this->class;
	}

}
