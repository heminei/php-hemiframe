<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Validation
{
    private $data;

    public function __construct($string = null)
    {
        if (null !== $string) {
            $this->setData($string);
        }
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function isNumber(): bool
    {
        if (is_numeric($this->data)) {
            return true;
        }

        return false;
    }

    public function isCyrillic(): bool
    {
        $pattern = "/^[\p{Cyrillic}0-9_.\s\-]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        }

        return false;
    }

    public function isCyrillicLettersOnly(): bool
    {
        $pattern = "/^[\p{Cyrillic}\s]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        }

        return false;
    }

    public function isLatin(): bool
    {
        $pattern = "/^[a-zA-Z0-9_.\s\-]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        }

        return false;
    }

    public function hasLatinLettersOnly(): bool
    {
        $pattern = "/^[a-zA-Z\s]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        }

        return false;
    }

    public function isEmail(bool $validateMxRecords = false): bool
    {
        if (!filter_var($this->data, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if (true === $validateMxRecords) {
            list($user, $domain) = explode('@', $this->data);
            if (!checkdnsrr($domain, 'MX')) {
                return false;
            }
        }

        return true;
    }

    public function isIp(): bool
    {
        if (filter_var($this->data, FILTER_VALIDATE_IP)) {
            return true;
        }

        return false;
    }

    public function isUrl(): bool
    {
        if (filter_var($this->data, FILTER_VALIDATE_URL)) {
            return true;
        }

        return false;
    }

    public function hasSpaces(): bool
    {
        if (preg_match('/\\s/', $this->data)) {
            return true;
        }

        return false;
    }

    public function hasSpecialSymbols(): bool
    {
        if (preg_match('/[\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\?\\\]/', $this->data)) {
            return true;
        }

        return false;
    }
}
