<?php

/**
 * @author heminei
 */

namespace HemiFrame\Interfaces;

interface Cache
{

    public function getKeyPrefix(): string;

    public function setKeyPrefix(string $keyPrefix);

    public function get($key, $default = null);

    public function set($key, $value, $time);

    public function delete($key): bool;

    public function exists(string $key): bool;
}
