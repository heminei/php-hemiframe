<?php

namespace HemiFrame\Lib;

/**
 * @version 1.2
 *
 * @author heminei <heminei@heminei.com>
 */
class StringEditor
{
    private $string = '';
    private $encoding = 'UTF-8';

    public function __construct($string = null)
    {
        if (null !== $string) {
            $this->setString($string);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getString();
    }

    /**
     * Select string.
     *
     * @return $this
     */
    public function setString(string $string): self
    {
        $this->string = $string;

        return $this;
    }

    /**
     * Get string.
     */
    public function getString(): string
    {
        return $this->string;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding(string $encoding): self
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Append string.
     *
     * @return $this
     */
    public function appendString(string $string): self
    {
        $this->string = $this->string.$string;

        return $this;
    }

    /**
     * Prepend string.
     *
     * @return $this
     */
    public function prependString(string $string): self
    {
        if (null !== $string) {
            $this->string = $string.$this->string;
        }

        return $this;
    }

    /**
     * Replace string.
     *
     * @return $this
     */
    public function replace(string $search, string $replace): self
    {
        $this->string = str_replace($search, $replace, $this->string);

        return $this;
    }

    /**
     * Remove string.
     *
     * @return $this
     */
    public function remove(string $string): self
    {
        $this->replace($string, '');

        return $this;
    }

    /**
     * Get lenght string.
     */
    public function getLength(): int
    {
        return mb_strlen($this->string, $this->getEncoding());
    }

    /**
     * Get last char.
     */
    public function getLastChar(): string
    {
        return mb_substr($this->getString(), -1, null, $this->getEncoding());
    }

    /**
     * Transliterate string.
     *
     * @return $this
     */
    public function transliterate(): self
    {
        $bg = [
            'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о',
            'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О',
            'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ь', 'Ю', 'Я',
        ];
        $en = [
            'a', 'b', 'v', 'g', 'd', 'e', 'j', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o',
            'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'st', 'y', 'i', 'iu', 'q',
            'A', 'B', 'V', 'G', 'D', 'E', 'J', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O',
            'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'St', 'Y', 'I', 'Iu', 'Q',
        ];
        $this->string = str_replace($bg, $en, $this->string);

        return $this;
    }

    /**
     * Transliterate string.
     *
     * @return $this
     */
    public function transliterateToBg(): self
    {
        $bg = [
            'Щ', 'щ', 'а', 'б', 'в', 'г', 'д', 'е', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о',
            'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ь', 'ю', 'я',
            'А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О',
            'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ь', 'Ю', 'Я',
        ];
        $en = [
            'Sht', 'sht', 'a', 'b', 'v', 'g', 'd', 'e', 'j', 'z', 'i', 'i', 'k', 'l', 'm', 'n', 'o',
            'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'st', 'y', 'i', 'iu', 'q',
            'A', 'B', 'V', 'G', 'D', 'E', 'J', 'Z', 'I', 'I', 'K', 'L', 'M', 'N', 'O',
            'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'St', 'Y', 'I', 'Iu', 'Q',
        ];
        $this->string = str_replace($en, $bg, $this->string);

        return $this;
    }

    /**
     * Short string.
     *
     * @return $this
     */
    public function cropString(int $length, bool $addSuffix = false, string $suffixContent = '...'): self
    {
        $string = mb_substr($this->string, 0, $length, $this->getEncoding());
        if (false === $addSuffix) {
            $string = $string.$suffixContent;
        }
        $this->string = $string;

        return $this;
    }

    /**
     * To url string.
     *
     * @return $this
     */
    public function toUrlString(): self
    {
        $this->string = htmlspecialchars_decode($this->string);
        $this->string = str_replace(' ', '-', $this->string);

        $from = [
            'Á', 'À', 'Â', 'Ä', 'Ă', 'Ā', 'Ã', 'Å', 'Ą', 'Æ', 'Ć', 'Ċ', 'Ĉ', 'Č', 'Ç', 'Ď', 'Đ', 'Ð',
            'É', 'È', 'Ė', 'Ê', 'Ë', 'Ě', 'Ē', 'Ę', 'Ə', 'Ġ', 'Ĝ', 'Ğ', 'Ģ', 'á', 'à', 'â', 'ä', 'ă', 'ā', 'ã', 'å', 'ą',
            'æ', 'ć', 'ċ', 'ĉ', 'č', 'ç', 'ď', 'đ', 'ð', 'é', 'è', 'ė', 'ê', 'ë', 'ě', 'ē', 'ę', 'ə', 'ġ', 'ĝ', 'ğ', 'ģ',
            'Ĥ', 'Ħ', 'I', 'Í', 'Ì', 'İ', 'Î', 'Ï', 'Ī', 'Į', 'Ĳ', 'Ĵ', 'Ķ', 'Ļ', 'Ł', 'Ń', 'Ň', 'Ñ', 'Ņ',
            'Ó', 'Ò', 'Ô', 'Ö', 'Õ', 'Ő', 'Ø', 'Ơ', 'Œ', 'ĥ', 'ħ', 'ı', 'í', 'ì', 'i', 'î', 'ï', 'ī', 'į',
            'ĳ', 'ĵ', 'ķ', 'ļ', 'ł', 'ń', 'ň', 'ñ', 'ņ', 'ó', 'ò', 'ô', 'ö', 'õ', 'ő', 'ø', 'ơ', 'œ', 'Ŕ', 'Ř',
            'Ś', 'Ŝ', 'Š', 'Ş', 'Ť', 'Ţ', 'Þ', 'Ú', 'Ù', 'Û', 'Ü', 'Ŭ', 'Ū', 'Ů', 'Ų', 'Ű', 'Ư', 'Ŵ', 'Ý', 'Ŷ', 'Ÿ',
            'Ź', 'Ż', 'Ž', 'ŕ', 'ř', 'ś', 'ŝ', 'š', 'ş', 'ß', 'ť', 'ţ', 'þ', 'ú', 'ù', 'û', 'ü', 'ŭ', 'ū', 'ů', 'ų', 'ű', 'ư',
            'ŵ', 'ý', 'ŷ', 'ÿ', 'ź', 'ż', 'ž',
        ];
        $to = [
            'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'A', 'AE', 'C', 'C', 'C', 'C', 'C', 'D', 'D', 'D',
            'E', 'E', 'E', 'E', 'E', 'E', 'E', 'E', 'G', 'G', 'G', 'G', 'G', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a', 'a',
            'ae', 'c', 'c', 'c', 'c', 'c', 'd', 'd', 'd', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'e', 'g', 'g', 'g', 'g', 'g',
            'H', 'H', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'I', 'IJ', 'J', 'K', 'L', 'L', 'N', 'N', 'N', 'N',
            'O', 'O', 'O', 'O', 'O', 'O', 'O', 'O', 'CE', 'h', 'h', 'i', 'i', 'i', 'i', 'i', 'i', 'i', 'i',
            'ij', 'j', 'k', 'l', 'l', 'n', 'n', 'n', 'n', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'o', 'R', 'R',
            'S', 'S', 'S', 'S', 'T', 'T', 'T', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'U', 'W', 'Y', 'Y', 'Y',
            'Z', 'Z', 'Z', 'r', 'r', 's', 's', 's', 's', 'B', 't', 't', 'b', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u', 'u',
            'w', 'y', 'y', 'y', 'z', 'z', 'z',
        ];
        $this->string = str_replace($from, $to, $this->string);
        $this->string = preg_replace('/[^A-Za-zа-яА-Я0-9-_+]+/iu', '', $this->string);

        return $this;
    }

    /**
     * To uppercase string.
     *
     * @return $this
     */
    public function toUppercase(): self
    {
        $this->string = mb_strtoupper($this->string, $this->getEncoding());

        return $this;
    }

    /**
     * To lowercase string.
     *
     * @return $this
     */
    public function toLowercase(): self
    {
        $this->string = mb_strtolower($this->string, $this->getEncoding());

        return $this;
    }

    /**
     * Trim string.
     *
     * @return $this
     */
    public function trim(): self
    {
        $this->string = trim($this->string);

        return $this;
    }

    /**
     * Explode string.
     */
    public function explode(string $delimiter): array
    {
        $array = explode($delimiter, $this->string);

        return $array;
    }

    /**
     * MD5 current string.
     *
     * @return $this
     */
    public function md5(): self
    {
        $this->string = md5($this->string);

        return $this;
    }

    /**
     * SHA1 current string.
     *
     * @return $this
     */
    public function sha1(): self
    {
        $this->string = sha1($this->string);

        return $this;
    }

    /**
     * Inserts HTML line breaks before all newlines in a string.
     *
     * @return $this
     */
    public function nl2br(bool $isXhtml = true): self
    {
        $this->string = nl2br($this->string, $isXhtml);

        return $this;
    }

    /**
     * Strip HTML and PHP tags from a string.
     *
     * @param string $allowableTags
     *
     * @return $this
     */
    public function stripTags($allowableTags = null): self
    {
        $this->string = strip_tags($this->string, $allowableTags);

        return $this;
    }

    /**
     * Return part of a string.
     *
     * @param int $length
     *
     * @return $this
     */
    public function substr(int $start, $length = null): self
    {
        $this->string = mb_substr($this->string, $start, $length);

        return $this;
    }

    /**
     * Wraps a string to a given number of characters.
     *
     * @param int    $width the number of characters at which the string will be wrapped
     * @param string $break the line is broken using the optional break parameter
     * @param bool   $cut   if the cut is set to TRUE, the string is always wrapped at or before the specified width
     *
     * @return $this
     */
    public function wordwrap(int $width = 75, string $break = "\n", bool $cut = false): self
    {
        $this->string = wordwrap($this->string, $width, $break, $cut);

        return $this;
    }

    /**
     * Generate random string (1234567890qwertyuiopasdfgjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM).
     *
     * @param int $length - string length
     *
     * @return $this
     */
    public function generateRandomString(int $length = 10): self
    {
        $symbols = '1234567890qwertyuiopasdfgjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM';
        $this->string = substr(str_shuffle($symbols), 0, $length);

        return $this;
    }
}
