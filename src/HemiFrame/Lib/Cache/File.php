<?php

namespace HemiFrame\Lib\Cache;

class File implements \HemiFrame\Interfaces\Cache {

    private $folder = "cache/";
    private $keyPrefix = "";

    public function __construct() {

    }

    public function getFolder(): string {
        return $this->folder;
    }

    /**
     *
     * @param string $folder
     * @return $this
     * @throws \Exception
     */
    public function setFolder(string $folder): self {
        if (!is_writable($folder)) {
            throw new \Exception("Folder is not writable ($folder)");
        }
        $this->folder = str_replace("//", "/", $folder . "/");

        return $this;
    }

    public function getKeyPrefix(): string {
        return $this->keyPrefix;
    }

    public function setKeyPrefix(string $keyPrefix): self {
        $this->keyPrefix = $keyPrefix;

        return $this;
    }

    public function getFile(string $key): string {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        return $this->folder . md5($this->keyPrefix . $key) . ".cache";
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
        if (!is_writable($this->getFolder())) {
            throw new \Exception("Can't be save file: " . $this->getFile($key));
        }
        $data = [
            "expiryTime" => time() + $time,
            "value" => $value,
        ];
        file_put_contents($this->getFile($key), serialize($data));

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
        if (!is_readable($this->getFile($key))) {

            return null;
        }
        $content = file_get_contents($this->getFile($key));
        $data = unserialize($content);

        if ($data['expiryTime'] > time()) {
            return $data['value'];
        }

        return null;
    }

    /**
     * @param string $key
     * @return boolean
     */
    public function delete(string $key): bool {
        if (empty($key)) {
            throw new \Exception("Enter key");
        }
        if (file_exists($this->getFile($key))) {
            unlink($this->getFile($key));
            return true;
        }
        return false;
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
        if (!is_readable($this->getFile($key))) {
            return false;
        }
        $content = file_get_contents($this->getFile($key));
        $data = unserialize($content);

        if ($data['expiryTime'] > time()) {
            return true;
        }
        return false;
    }

}
