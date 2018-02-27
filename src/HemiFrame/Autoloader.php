<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Autoloader {

	private static $loadedClasses = array();
	private static $namespaces = array();
	private static $directorySeparator = DIRECTORY_SEPARATOR;

	private function __construct() {

	}

	/**
	 *
	 * @return array
	 */
	public static function getLoadedClasses(): array {
		return self::$loadedClasses;
	}

	/**
	 *
	 * @return \spl_autoload_register
	 */
	public static function register(): bool {
		return spl_autoload_register(array(__CLASS__, "autoloader"));
	}

	/**
	 *
	 * @param string $class
	 * @throws \Exception
	 */
	public static function autoloader(string $class) {
		foreach (self::$namespaces as $k => $v) {
			if (strpos($class, $k) === 0) {
				$filePath = substr_replace(str_replace('\\', self::$directorySeparator, $class), $v, 0, strlen($k)) . '.php';
				$file = realpath($filePath);
				if (is_readable($file)) {
					include $file;
					self::$loadedClasses[] = $class;
				} else {
					throw new \Exception('File cannot be included:' . $filePath . "; Class: $class");
				}
				break;
			}
		}
	}

	/**
	 *
	 * @param string $namespace
	 * @param string $path
	 * @throws \Exception
	 */
	public static function registerNamespace(string $namespace, string $path) {
		$namespace = trim($namespace);
		if (strlen($namespace) > 0) {
			if (!isset($path)) {
				throw new \Exception('Invalid path');
			}
			$_path = realpath($path);
			if ($_path && is_dir($_path) && is_readable($_path)) {
				self::$namespaces[$namespace . '\\'] = $_path . self::$directorySeparator;
			} else {
				throw new \Exception('Namespace directory read error:' . $path);
			}
		} else {
			throw new \Exception('Invalid namespace:' . $namespace);
		}
	}

	/**
	 *
	 * @param array $array
	 * @throws \Exception
	 */
	public static function registerNamespaces(array $array) {
		if (is_array($array)) {
			foreach ($array as $k => $v) {
				self::registerNamespace($k, $v);
			}
		} else {
			throw new \Exception('Invalid namespaces');
		}
	}

	/**
	 *
	 * @return array
	 */
	public static function getNamespaces(): array {
		return self::$namespaces;
	}

	/**
	 *
	 * @param string $namespace
	 */
	public static function removeNamespace(string $namespace) {
		unset(self::$namespaces[$namespace]);
	}

	public static function clearNamespaces() {
		self::$namespaces = array();
	}

	/**
	 *
	 * @return string
	 */
	public static function getDirectorySeparator(): string {
		return self::$directorySeparator;
	}

	/**
	 *
	 * @param string $directorySeparator
	 */
	public static function setDirectorySeparator(string $directorySeparator) {
		self::$directorySeparator = $directorySeparator;
	}

}
