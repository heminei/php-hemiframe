<?php

namespace HemiFrame\Lib\Minifier;

/**
 * @author heminei <heminei@heminei.com>
 * @version 3.0
 */
class JS
{
    private $files = [];
    private $content = "";

    public function __construct()
    {
    }

    public function __toString()
    {
        return $this->getContent();
    }

    public function addFile(string $filePath): self
    {
        if (!file_exists($filePath)) {
            throw new \Exception("File " . $filePath . " not found.");
        }
        $fileContent = file_get_contents($filePath);
        $fileMd5 = md5($fileContent);
        $check = array_filter($this->files, function ($file) use ($fileMd5) {
            if ($file['md5'] == $fileMd5) {
                return true;
            }
            return false;
        });
        if (count($check) == 0) {
            $this->files[] = [
                "path" => $filePath,
                "content" => $fileContent,
                "md5" => $fileMd5
            ];
            $this->content .= $fileContent . "
";
        }
        return $this;
    }

    public function addPath(string $path, string $fileTypes = "js"): self
    {
        $fileTypesArray = explode(",", $fileTypes);
        if (!is_dir($path)) {
            throw new \Exception("Path is not valid.");
        }
        $scandir = scandir($path);
        $cdir = array_slice($scandir, 2);
        foreach ($cdir as $file) {
            $filePath = str_replace("//", "/", $path . "/" . $file);
            if (is_file($filePath)) {
                $pathinfo = pathinfo($filePath);
                if (in_array($pathinfo['extension'], $fileTypesArray)) {
                    $this->addFile($filePath);
                }
            } elseif (is_dir($filePath)) {
                $this->addPath($filePath, $fileTypes);
            }
        }

        return $this;
    }

    public function addString(string $string): self
    {
        if ($string !== null) {
            $this->files[] = [
                "path" => null,
                "content" => $string,
                "md5" => md5($string),
            ];
            $this->content .= $string . PHP_EOL;
        }
        return $this;
    }

    public function compress(): self
    {
        $this->content = $this->getContent();

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    public function saveToFile(string $filePath): bool
    {
        if (file_put_contents($filePath, $this->getContent())) {
            return true;
        } else {
            return false;
        }
    }
}
