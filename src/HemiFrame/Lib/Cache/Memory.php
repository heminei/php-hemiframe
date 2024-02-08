<?php

namespace HemiFrame\Lib\Cache;

/**
 * @author Heminei
 */
class Memory implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private static $data = [];
    private $keyPrefix = '';
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
     * @param string $key
     * @param int    $time
     *
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $time = null): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        if (null === $time) {
            $time = $this->defaultTtl;
        }
        self::$data[$this->keyPrefix.$key] = [
            'expiryTime' => time() + $time,
            'value' => $value,
        ];

        return true;
    }

    /**
     * @param string $key
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Key is empty');
        }
        if (isset(self::$data[$this->keyPrefix.$key])) {
            if (self::$data[$this->keyPrefix.$key]['expiryTime'] > time()) {
                return self::$data[$this->keyPrefix.$key]['value'];
            }
        }

        return $default;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        unset(self::$data[$this->keyPrefix.$key]);

        return true;
    }

    public function clear(): bool
    {
        self::$data = [];

        return true;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        if (isset(self::$data[md5($this->keyPrefix.$key)])) {
            if (self::$data[md5($this->keyPrefix.$key)]['expiryTime'] > time()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @throws \Exception
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param array $keys
     */
    public function getMultiple($keys, $default = null): array
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException('Keys must be array');
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
            throw new InvalidArgumentException('Values must be array');
        }

        $result = true;
        foreach ($values as $key => $value) {
            if (false == $this->set($key, $value, $ttl)) {
                $result = false;
            }
        }

        return $result;
    }

    public function deleteMultiple($keys)
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException('Keys must be array');
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }
}
