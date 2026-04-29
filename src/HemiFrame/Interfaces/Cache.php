<?php

/**
 * @author heminei
 */

namespace HemiFrame\Interfaces;

interface Cache extends \Psr\SimpleCache\CacheInterface
{
    public function getKeyPrefix(): string;

    public function setKeyPrefix(string $keyPrefix);

    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, int|\DateInterval|null $ttl = null): bool;

    public function delete(string $key): bool;

    public function exists(string $key): bool;
}
