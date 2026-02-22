<?php

namespace HemiFrame\Lib;

class Image
{
    /**
     * @var \Imagick
     */
    private $image;

    public function __construct(?string $img = null)
    {
        $this->image = new \Imagick();
        if (null !== $img) {
            $this->load($img);
        }
    }

    public function __destruct()
    {
        $this->image->clear();
        $this->image->destroy();
    }

    public function __toString()
    {
        return $this->getImage();
    }

    public function load(string $img)
    {
        $this->image = new \Imagick($img);
        if ('gif' == $this->getType()) {
            $this->image = $this->image->coalesceImages();
        }
    }

    public function setFormat(string $format): self
    {
        $this->image->setImageFormat($format);
        if ('gif' != $this->getType()) {
            $this->image = $this->image->getImage();
        }

        return $this;
    }

    public function setCompression(int $quality): self
    {
        $this->image->setImageCompressionQuality($quality);

        return $this;
    }

    public function save(string $filepath): bool
    {
        if ('gif' == $this->getType()) {
            return $this->image->writeImages($filepath, true);
        }

        return $this->image->writeImage($filepath);
    }

    public function output()
    {
        header('Content-type: '.$this->getMimeType());
        echo $this->getImage();
    }

    public function getImagickObject()
    {
        return $this->image;
    }

    public function setImagickObject(\Imagick $imagick)
    {
        $this->image = $imagick;
    }

    public function getImage(): string
    {
        return (string) $this->image->getImagesBlob();
    }

    public function getType(): string
    {
        return $this->image->getimageformat();
    }

    public function getMimeType(): string
    {
        return $this->image->getImageMimeType();
    }

    public function getWidth(): int
    {
        return $this->image->getImageWidth();
    }

    public function getHeight(): int
    {
        return $this->image->getImageHeight();
    }

    public function getSize(string $type = 'B'): float
    {
        $size = $this->image->getImageLength();

        if ('KB' == $type) {
            return round($size / 1024, 2);
        } elseif ('MB' == $type) {
            return round($size / 1024 / 1024, 2);
        }

        return $size;
    }

    public function resizeToHeight(int $height): self
    {
        return $this->resize(0, $height);
    }

    public function resizeToWidth(int $width): self
    {
        return $this->resize($width, 0);
    }

    public function scale(float $scale)
    {
        $width = (int) ($this->getWidth() * $scale / 100);
        $height = (int) ($this->getheight() * $scale / 100);

        return $this->resize($width, $height);
    }

    public function resize(int $width, int $height): self
    {
        foreach ($this->image as $image) {
            $image->scaleImage($width, $height);
        }

        return $this;
    }

    public function crop(int $width, int $height, int $x = 0, int $y = 0): self
    {
        foreach ($this->image as $image) {
            $image->cropImage($width, $height, $x, $y);
        }

        return $this;
    }

    public function adaptiveResize(int $width, int $height): self
    {
        foreach ($this->image as $image) {
            $image->cropThumbnailImage($width, $height);
        }

        return $this;
    }

    public function stripImage(): self
    {
        $this->image->stripImage();

        return $this;
    }

    public function rotate(float $deg, $background = '#00000000'): self
    {
        foreach ($this->image as $image) {
            $image->rotateImage(new \ImagickPixel($background), $deg);
        }

        return $this;
    }
}
