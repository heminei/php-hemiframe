<?php

namespace HemiFrame\Lib\Cache;

class Redis implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private \Redis $redis;
    private string $keyPrefix = '';
    private int $defaultTtl = 120;

    public function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self
    {
        $this->keyPrefix = $keyPrefix;

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

        return $this->redis->set($this->keyPrefix.$key, serialize($value), ['ex' => $ttl]);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Key is empty');
        }
        $data = $this->redis->get($this->keyPrefix.$key);
        if (false !== $data) {
            return unserialize($data);
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

        return (bool) $this->redis->del($this->keyPrefix.$key);
    }

    public function clear(): bool
    {
        // Only deletes keys with the current prefix
        $pattern = $this->keyPrefix.'*';
        $it = null;
        $keys = [];
        while ($arr_keys = $this->redis->scan($it, $pattern)) {
            $keys = array_merge($keys, $arr_keys);
        }
        if (!empty($keys)) {
            $this->redis->del($keys);
        }

        return true;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        $exists = $this->redis->exists($this->keyPrefix.$key);

        if (!is_int($exists)) {
            return false;
        }

        return $exists > 0;
    }

    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    public function getMultiple(iterable $keys, mixed $default = null): array
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException('Keys must be array');
        }

        $prefixedKeys = array_map(fn ($key) => $this->keyPrefix.$key, $keys);
        $results = $this->redis->mget($prefixedKeys);
        $data = [];
        foreach ($keys as $i => $key) {
            $value = $results[$i];
            $data[$key] = (false !== $value) ? unserialize($value) : $default;
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

        $prefixedKeys = array_map(fn ($key) => $this->keyPrefix.$key, $keys);
        $this->redis->del($prefixedKeys);

        return true;
    }

    public function getRedis(): \Redis
    {
        return $this->redis;
    }
}
