<?php

namespace HemiFrame\Lib\Cache;

class Apc implements \HemiFrame\Interfaces\Cache {

    private $keyPrefix = "";

    public function __construct() {

    }

    public function getKeyPrefix(): string {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self {
        $this->keyPrefix = $keyPrefix;

        return $this;
    }

    public function checkApcExtension(): bool {
        $check = extension_loaded('apcu');
        return $check;
    }

    /**
     *
     * @param string $key
     * @param mixed $value
     * @param int $time
     * @return $this
     * @throws \Exception
     */
    public function set(string $key, $value, int $time): self {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }

        \apcu_add(md5($this->keyPrefix . $key), serialize($value), $time);

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function get(string $key) {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        $data = apcu_fetch(md5($this->keyPrefix . $key));
        if ($data !== false) {
            return unserialize($data);
        }

        return null;
    }

    /**
     *
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function delete(string $key): bool {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        return apcu_delete(md5($this->keyPrefix . $key));
    }

    /**
     *
     * @param string $key
     * @return bool
     * @throws \Exception
     */
    public function exists(string $key): bool {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        return apcu_exists(md5($this->keyPrefix . $key));
    }

}
