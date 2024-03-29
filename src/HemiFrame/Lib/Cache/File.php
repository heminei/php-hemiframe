<?php

namespace HemiFrame\Lib\Cache;

class File implements \HemiFrame\Interfaces\Cache, \Psr\SimpleCache\CacheInterface
{
    private $folder = 'tmp/cache/';
    private $keyPrefix = '';
    private $defaultTtl = 120;

    public function getFolder(): string
    {
        return $this->folder;
    }

    /**
     * @return $this
     *
     * @throws \Exception
     */
    public function setFolder(string $folder): self
    {
        if (!is_writable($folder)) {
            throw new \Exception("Folder is not writable ($folder)");
        }
        $this->folder = str_replace('//', '/', $folder.'/');

        return $this;
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

    public function getFile(string $key): string
    {
        if (empty($key)) {
            throw new \Exception('Enter key');
        }

        return $this->folder.md5($this->keyPrefix.$key).'.cache';
    }

    /**
     * @param string $key
     * @param int    $time
     *
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $time = null): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        if (null === $time) {
            $time = $this->defaultTtl;
        }

        if (!is_writable($this->getFolder())) {
            throw new \RuntimeException("Can't be save file: ".$this->getFile($key));
        }
        $data = [
            'expiryTime' => time() + $time,
            'value' => $value,
        ];

        return false !== file_put_contents($this->getFile($key), serialize($data));
    }

    /**
     * @param string $key
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Key is empty');
        }
        if (!is_readable($this->getFile($key))) {
            return $default;
        }

        $content = file_get_contents($this->getFile($key));
        $data = unserialize($content);

        if ($data['expiryTime'] > time()) {
            return $data['value'];
        }

        return $default;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function delete($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
        }
        if (file_exists($this->getFile($key))) {
            unlink($this->getFile($key));

            return true;
        }

        return false;
    }

    public function clear(): bool
    {
        if (!is_readable($this->getFolder())) {
            throw new \RuntimeException('Cache folder is not readable');
        }

        $files = array_diff(scandir($this->getFolder()), ['.', '..']);
        foreach ($files as $value) {
            $extension = pathinfo($value, PATHINFO_EXTENSION);
            if ('cache' == $extension) {
                unset($value);
            }
        }

        return true;
    }

    /**
     * @param string $key
     *
     * @throws InvalidArgumentException
     */
    public function has($key): bool
    {
        if (empty($key)) {
            throw new InvalidArgumentException('Enter key');
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

    /**
     * @throws InvalidArgumentException
     */
    public function exists(string $key): bool
    {
        return $this->has($key);
    }

    /**
     * @param array $keys
     */
    public function getMultiple($keys, $default = null): array
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

    public function setMultiple($values, $ttl = null): bool
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

    public function deleteMultiple($keys)
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
