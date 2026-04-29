<?php

namespace HemiFrame\Lib\Cache;

/**
 * @author Heminei
 */
class Memory implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private static array $data = [];
    private string $keyPrefix = '';
    private int $defaultTtl = 120;

    public function getKeyPrefix(): string
    {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): void
    {
        $this->keyPrefix = $keyPrefix;
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
        self::$data[$this->keyPrefix.$key] = [
            'expiryTime' => time() + $ttl,
            'value' => $value,
        ];

        return true;
    }

    public function get(string $key, mixed $default = null): mixed
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
     * @throws InvalidArgumentException
     */
    public function delete(string $key): bool
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
     * @throws InvalidArgumentException
     */
    public function has(string $key): bool
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
