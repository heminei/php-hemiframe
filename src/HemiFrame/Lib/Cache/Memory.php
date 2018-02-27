<?php

namespace HemiFrame\Lib\Cache;

/**
 * @author Heminei
 */
class Memory implements \HemiFrame\Interfaces\Cache {

    private $data = [];
    private $keyPrefix = "";

    public function delete(string $key): bool {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        unset($this->data[$key]);

        return true;
    }

    public function get(string $key) {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        if (isset($this->data[md5($this->keyPrefix . $key)])) {
            if ($this->data[md5($this->keyPrefix . $key)]['expiryTime'] > time()) {
                return $this->data[md5($this->keyPrefix . $key)]['value'];
            }
        }
        return null;
    }

    public function getKeyPrefix(): string {
        return $this->keyPrefix;
    }

    public function set(string $key, $value, int $time) {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        $this->data[md5($this->keyPrefix . $key)] = [
            "expiryTime" => time() + $time,
            "value" => $value,
        ];

        return $this;
    }

    public function setKeyPrefix(string $keyPrefix) {
        $this->keyPrefix = $keyPrefix;
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
        if (isset($this->data[md5($this->keyPrefix . $key)])) {
            if ($this->data[md5($this->keyPrefix . $key)]['expiryTime'] > time()) {
                return true;
            }
        }
        return false;
    }

}
