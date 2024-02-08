<?php

namespace HemiFrame;

/**
 * @author heminei <heminei@heminei.com>
 */
class Autoloader
{
    private static $loadedClasses = [];
    private static $namespaces = [];
    private static $directorySeparator = DIRECTORY_SEPARATOR;

    private function __construct()
    {
    }

    public static function getLoadedClasses(): array
    {
        return self::$loadedClasses;
    }

    public static function register(): bool
    {
        return spl_autoload_register([__CLASS__, 'autoloader']);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function autoloader(string $class)
    {
        foreach (self::$namespaces as $k => $v) {
            if (0 === strpos($class, $k) || (!strstr($class, '\\') && '\\' === $k)) {
                if ('\\' === $k) {
                    $class = '\\'.$class;
                }
                $filePath = substr_replace(str_replace('\\', self::$directorySeparator, $class), $v, 0, strlen($k)).'.php';
                $file = realpath($filePath);
                if (is_readable($file)) {
                    include $file;
                    self::$loadedClasses[] = $class;
                } else {
                    throw new \InvalidArgumentException('File cannot be included:'.$filePath."; Class: $class");
                }
                break;
            }
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function registerNamespace(string $namespace, string $path)
    {
        $namespace = trim($namespace);
        if (empty($path)) {
            throw new \InvalidArgumentException('Invalid path');
        }
        $_path = realpath($path);
        if ($_path && is_dir($_path) && is_readable($_path)) {
            self::$namespaces[$namespace.'\\'] = $_path.self::$directorySeparator;
        } else {
            throw new \InvalidArgumentException('Namespace directory read error:'.$path);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function registerNamespaces(array $array)
    {
        foreach ($array as $k => $v) {
            self::registerNamespace($k, $v);
        }
    }

    public static function getNamespaces(): array
    {
        return self::$namespaces;
    }

    public static function removeNamespace(string $namespace)
    {
        unset(self::$namespaces[$namespace]);
    }

    public static function clearNamespaces()
    {
        self::$namespaces = [];
    }

    public static function getDirectorySeparator(): string
    {
        return self::$directorySeparator;
    }

    public static function setDirectorySeparator(string $directorySeparator)
    {
        self::$directorySeparator = $directorySeparator;
    }
}
