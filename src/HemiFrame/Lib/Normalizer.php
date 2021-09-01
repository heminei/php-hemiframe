<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Normalizer
{
    private $data = null;

    public function __construct($data = null)
    {
        if ($data !== null) {
            $this->setData($data);
        }
    }

    /**
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     *
     * @param mixed $data
     * @return self
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Convert to int
     * @return self
     */
    public function toint(): self
    {
        $this->data = (int) $this->data;

        return $this;
    }

    /**
     * Convert to float
     * @return self
     */
    public function tofloat(): self
    {
        $this->data = (float) $this->data;

        return $this;
    }

    /**
     * Convert to double
     * @return self
     */
    public function todouble(): self
    {
        $this->data = (float) $this->data;

        return $this;
    }

    /**
     * Convert to bool
     * @return self
     */
    public function tobool(): self
    {
        $this->data = $this->data == true;

        return $this;
    }

    /**
     * Convert to string
     * @return self
     */
    public function tostring(): self
    {
        $this->data = (string) $this->data;

        return $this;
    }

    /**
     * Strip whitespace (or other characters) from the beginning and end of a string
     * @return self
     */
    public function trim(): self
    {
        $this->data = trim($this->data);

        return $this;
    }

    /**
     * Convert all applicable characters to HTML entities
     * @return self
     */
    public function htmlentities(): self
    {
        $this->data = htmlentities($this->data);

        return $this;
    }

    /**
     *
     * @return self
     */
    public function normalize(string $types): self
    {
        $typesArray = explode('|', $types);
        foreach ($typesArray as $type) {
            if (in_array($type, ["int", "tofloat", "todouble", "tobool", "tostring"])) {
                $type = "to" . $type;
            }
            if (method_exists($this, $type)) {
                $this->$type();
            }
        }
        return $this;
    }

    /**
     *
     * @return self
     */
    public function xss(): self
    {
        // Fix &entity\n;
        $data = $this->getData();
        $data = str_replace(['&amp;', '&lt;', '&gt;'], ['&amp;amp;', '&amp;lt;', '&amp;gt;'], $data);
        $data = preg_replace('/(&#*\w+)[\x00-\x20]+;/u', '$1;', $data);
        $data = preg_replace('/(&#x*[0-9A-F]+);*/iu', '$1;', $data);
        $data = html_entity_decode($data, ENT_COMPAT, 'UTF-8');

        // Remove any attribute starting with "on" or xmlns
        $data = preg_replace('#(<[^>]+?[\x00-\x20"\'])(?:on|xmlns)[^>]*+>#iu', '$1>', $data);

        // Remove javascript: and vbscript: protocols
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=[\x00-\x20]*([`\'"]*)[\x00-\x20]*j[\x00-\x20]*a[\x00-\x20]*v[\x00-\x20]*a[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2nojavascript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*v[\x00-\x20]*b[\x00-\x20]*s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:#iu', '$1=$2novbscript...', $data);
        $data = preg_replace('#([a-z]*)[\x00-\x20]*=([\'"]*)[\x00-\x20]*-moz-binding[\x00-\x20]*:#u', '$1=$2nomozbinding...', $data);

        // Only works in IE: <span style="width: expression(alert('Ping!'));"></span>
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?expression[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?behaviour[\x00-\x20]*\([^>]*+>#i', '$1>', $data);
        $data = preg_replace('#(<[^>]+?)style[\x00-\x20]*=[\x00-\x20]*[`\'"]*.*?s[\x00-\x20]*c[\x00-\x20]*r[\x00-\x20]*i[\x00-\x20]*p[\x00-\x20]*t[\x00-\x20]*:*[^>]*+>#iu', '$1>', $data);

        // Remove namespaced elements (we do not need them)
        $data = preg_replace('#</*\w+:\w[^>]*+>#i', '', $data);

        do {
            // Remove really unwanted tags
            $old_data = $data;
            $data = preg_replace('#</*(?:applet|b(?:ase|gsound|link)|embed|frame(?:set)?|i(?:frame|layer)|l(?:ayer|ink)|meta|object|s(?:cript|tyle)|title|xml)[^>]*+>#i', '', $data);
        } while ($old_data !== $data);

        $this->data = $data;

        return $this;
    }
}
