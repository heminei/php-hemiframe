<?php

namespace HemiFrame\Lib\Cache;

class Memcached implements \HemiFrame\Interfaces\Cache {

    private $keyPrefix = "";

    /**
     *
     * @var \Memcached
     */
    private $memcached = null;

    public function __construct() {

    }

    public function getKeyPrefix(): string {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self {
        $this->keyPrefix = $keyPrefix;

        return $this;
    }

    public function getMemcached(): \Memcached {
        return $this->memcached;
    }

    public function setMemcached(\Memcached $memcached): self {
        $this->memcached = $memcached;

        return $this;
    }

    /**
     *
     * @param string $key
     * @param type $value
     * @param int $time
     * @return \self
     * @throws \Exception
     */
    public function set(string $key, $value, int $time): self {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        $this->memcached->set(md5($this->keyPrefix . $key), $value, $time);

        return $this;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key) {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        $data = $this->memcached->get(md5($this->keyPrefix . $key));
        if ($data !== false) {
            return $data;
        }
        return null;
    }

    /**
     * Removes a stored variable from the cache
     * @throws \Exception
     * @return boolean
     */
    public function delete(string $key): bool {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        return $this->memcached->delete(md5($this->keyPrefix . $key));
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
        $this->get($key);

        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return false;
        }

        return true;
    }

}
