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
        if ($string !== null) {
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

    /**
     *
     * @return boolean
     */
    public function isNumber(): bool
    {
        if (is_numeric($this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isCyrillic(): bool
    {
        $pattern = "/^[\p{Cyrillic}0-9_.\s\-]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isCyrillicLettersOnly(): bool
    {
        $pattern = "/^[\p{Cyrillic}\s]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isLatin(): bool
    {
        $pattern = "/^[a-zA-Z0-9_.\s\-]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function hasLatinLettersOnly(): bool
    {
        $pattern = "/^[a-zA-Z\s]+$/u";
        if (preg_match($pattern, $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isEmail(bool $validateMxRecords = false): bool
    {
        if (!filter_var($this->data, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        if ($validateMxRecords === true) {
            list($user, $domain) = explode('@', $this->data);
            if (!checkdnsrr($domain, "MX")) {
                return false;
            }
        }
        return true;
    }

    /**
     *
     * @return boolean
     */
    public function isIp(): bool
    {
        if (filter_var($this->data, FILTER_VALIDATE_IP)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function isUrl(): bool
    {
        if (filter_var($this->data, FILTER_VALIDATE_URL)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function hasSpaces(): bool
    {
        if (preg_match("/\\s/", $this->data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     */
    public function hasSpecialSymbols(): bool
    {
        if (preg_match('/[\'\/~`\!@#\$%\^&\*\(\)\+=\{\}\[\]\|;:"\<\>,\?\\\]/', $this->data)) {
            return true;
        } else {
            return false;
        }
    }
}
