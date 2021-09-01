<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class File
{
    private $name;
    private $size;
    private $type;
    private $md5;
    private $mimeType;
    private $path;
    private $destinationPath;

    public function __construct($file = null)
    {
        if ($file !== null) {
            $this->setFile($file);
        }
    }

    /**
     *
     * @param array|string $file
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setFile($file): self
    {
        if (is_array($file)) {
            if (empty($file['tmp_name'])) {
                throw new \InvalidArgumentException("Invalid file array. Array must be _FILES[file]");
            }
            $pathinfo = pathinfo($file['name']);
            $this->path = $file['tmp_name'];
            $this->name = $file['name'];
            $this->size = $file['size'];
            $this->mimeType = $file['type'];
        } elseif (is_string($file)) {
            if (!is_readable($file)) {
                throw new \InvalidArgumentException("$file - The file can't be read");
            }
            $pathinfo = pathinfo($file);
            $this->path = $file;
            $this->name = $pathinfo['basename'];
            $this->size = filesize($file);
            $this->mimeType = mime_content_type($file);
        /* @phpstan-ignore-next-line */
        } elseif ($file instanceof \Psr\Http\Message\UploadedFileInterface) {
            /** @var \Psr\Http\Message\UploadedFileInterface $file */
            $pathinfo = pathinfo($file->getClientFilename());
            $this->path = $file->getStream()->getMetadata('uri');
            $this->name = $file->getClientFilename();
            $this->size = $file->getSize();
            $this->mimeType = $file->getClientMediaType();
        } else {
            throw new \InvalidArgumentException("Invalid file");
        }
        $this->md5 = md5_file($this->path);
        $this->type = strtolower($pathinfo['extension']);

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getMd5(): string
    {
        return $this->md5;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDestinationPath()
    {
        return $this->destinationPath;
    }

    /**
     *
     * @param string $path
     * @param int $mode
     * @return $this
     */
    public function setDestinationPath(string $path, $mode = 0777): self
    {
        $this->destinationPath = $path;
        $dir = dirname($path);
        if (!is_dir($dir)) {
            if (!mkdir($dir, $mode, true)) {
                throw new \Exception("Can't generate destination path: $path");
            }
        }

        return $this;
    }

    /**
     *
     * @param string $folder
     * @param int $mode
     * @return self
     * @throws \Exception
     */
    public function generateDestinationPath(string $folder, $mode = 0777): self
    {
        $y = date('Y');
        $w = date('m');
        $j = date('j');
        $path = str_replace("//", "/", "$folder/$y/$w/$j/");
        if (!file_exists($path) && !mkdir($path, $mode, true)) {
            throw new \Exception("Can't generate destination folder: $path");
        }
        $this->destinationPath = $path . $this->md5 . "." . $this->type;

        return $this;
    }

    public function copy(): bool
    {
        if (empty($this->destinationPath)) {
            throw new \Exception("Destination Path is empty.");
        }
        return copy($this->path, $this->destinationPath);
    }

    public function moveUploadedFile(): bool
    {
        if (empty($this->destinationPath)) {
            throw new \Exception("Destination Path is empty.");
        }
        return move_uploaded_file($this->path, $this->destinationPath);
    }
}
