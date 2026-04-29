<?php

namespace HemiFrame\Lib\Cache;

class Memcached implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private string $keyPrefix = '';
    private int $defaultTtl = 120;
    private \Memcached $memcached;

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
     * @throws InvalidArgumentException
     */
    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        if ($ttl instanceof \DateInterval) {
            $ttl = (int) (new \DateTime())->add($ttl)->getTimestamp() - time();
        }
        if (null === $ttl) {
            $ttl = $this->defaultTtl;
        }

        return $this->memcached->set($this->keyPrefix.$key, $value, $ttl);
    }

    public function get(string $key, mixed $default = null): mixed
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
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
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
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
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

    public function getMultiple(iterable $keys, mixed $default = null): array
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

    public function setMultiple(iterable $values, int|\DateInterval|null $ttl = null): bool
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

    public function deleteMultiple(iterable $keys): bool
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
