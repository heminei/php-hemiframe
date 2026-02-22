<?php

namespace HemiFrame\Lib\Cache;

class Memcached implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private $keyPrefix = '';
    private $defaultTtl = 120;

    /**
     * @var \Memcached
     */
    private $memcached;

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

        return $this->memcached->set($this->keyPrefix.$key, $value, $time);
    }

    /**
     * @param string $key
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Key is empty');
        }

        $data = $this->memcached->get($this->keyPrefix.$key);
        if (false !== $data) {
            return $default;
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

        return $this->memcached->delete($this->keyPrefix.$key);
    }

    public function clear(): bool
    {
        return $this->memcached->flush();
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
        $this->get($key);

        if (\Memcached::RES_NOTFOUND === $this->memcached->getResultCode()) {
            return false;
        }

        return true;
    }

    /**
     * @throws \Exception
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param array|mixed $keys
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

    public function deleteMultiple($keys): bool
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
