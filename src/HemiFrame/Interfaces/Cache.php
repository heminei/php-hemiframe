<?php

/**
 * @author heminei
 */

namespace HemiFrame\Interfaces;

interface Cache {

    public function getKeyPrefix(): string;

    public function setKeyPrefix(string $keyPrefix);

    public function get(string $key);

    public function set(string $key, $value, int $time);

    public function delete(string $key): bool;

    public function exists(string $key): bool;
}
