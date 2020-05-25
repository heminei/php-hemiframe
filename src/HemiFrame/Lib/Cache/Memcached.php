<?php

namespace HemiFrame\Lib\Cache;

class Memcached implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{

    private $keyPrefix = "";
    private $defaultTtl = 120;

    /**
     *
     * @var \Memcached
     */
    private $memcached = null;

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self
    {
        $this->keyPrefix = $keyPrefix;

        return $this;
    }

    public function getMemcached(): \Memcached
    {
        return $this->memcached;
    }

    public function setMemcached(\Memcached $memcached): self
    {
        $this->memcached = $memcached;

        return $this;
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

        return $this->memcached->set($this->keyPrefix . $key, $value, $time);
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

        $data = $this->memcached->get($this->keyPrefix . $key);
        if ($data !== false) {
            return $default;
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
        return $this->memcached->delete($this->keyPrefix . $key);
    }

    /**
     * @return boolean
     */
    public function clear(): bool
    {
        return $this->memcached->flush();
    }

    /**
     *
     * @param string $key
     * @return bool
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException("Enter key");
        }
        $this->get($key);

        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return false;
        }

        return true;
    }

    /**
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
