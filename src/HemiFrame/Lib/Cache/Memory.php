<?php

namespace HemiFrame\Lib\Cache;

/**
 * @author Heminei
 */
class Memory implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private static $data = [];
    private $keyPrefix = "";
    private $defaultTtl = 120;

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix)
    {
        $this->keyPrefix = $keyPrefix;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @param int $time
     * @return bool
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $time = null): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Enter key");
        }
        if ($time === null) {
            $time = $this->defaultTtl;
        }
        static::$data[$this->keyPrefix . $key] = [
            "expiryTime" => time() + $time,
            "value" => $value,
        ];

        return true;
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Key is empty");
        }
        if (isset(static::$data[$this->keyPrefix . $key])) {
            if (static::$data[$this->keyPrefix . $key]['expiryTime'] > time()) {
                return static::$data[$this->keyPrefix . $key]['value'];
            }
        }
        return $default;
    }

    /**
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Enter key");
        }
        unset(static::$data[$this->keyPrefix . $key]);

        return true;
    }

    /**
     * @return boolean
     */
    public function clear(): bool
    {
        static::$data = [];
        return true;
    }

    /**
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Enter key");
        }
        if (isset(static::$data[$this->keyPrefix . $key])) {
            if (static::$data[$this->keyPrefix . $key]['expiryTime'] > time()) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param array $keys
     * @param mixed $default
     * @return array
     */
    public function getMultiple($keys, $default = null): array
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException("Keys must be array");
        }

        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key, $default);
        }

        return $data;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException("Values must be array");
        }

        $result = true;
        foreach ($values as $key => $value) {
            if ($this->set($key, $value, $ttl) == false) {
                $result = false;
            }
        }

        return $result;
    }

    public function deleteMultiple($keys)
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException("Keys must be array");
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }
}
