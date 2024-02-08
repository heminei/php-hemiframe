<?php

namespace HemiFrame\Lib;

/**
 * Create tag.
 *
 * @author Heminei
 */
class Tag
{
    private $tag;
    private $attr = [];
    private $attrValues = [];
    private $content;
    private $selfClose = false;
    private $selfCloseTagList = ['img', 'link', 'meta', 'br', 'hr', 'input'];

    public function __construct($tag = null)
    {
        if (null !== $tag) {
            $this->setTag($tag);
        }
    }

    public function __toString()
    {
        return $this->build();
    }

    /**
     * Get string.
     */
    public function getTag(): string
    {
        return $this->tag;
    }

    /**
     * Set string.
     *
     * @return $this
     */
    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        if (in_array($this->tag, $this->selfCloseTagList)) {
            $this->setSelfClose(true);
        }

        return $this;
    }

    /**
     * Get attr value.
     */
    public function getAttr(string $attr): string
    {
        if (isset($this->attrValues[$attr])) {
            return $this->attrValues[$attr];
        } else {
            return '';
        }
    }

    /**
     * Set attr.
     *
     * @return $this
     */
    public function setAttr(string $attr, $value): self
    {
        $this->attr[] = $attr;
        $this->attrValues[$attr] = htmlentities($value);

        return $this;
    }

    /**
     * Get inner content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set inner content.
     *
     * @return $this
     */
    public function setContent($content): self
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @return $this
     */
    public function appendContent($content): self
    {
        $this->content = $this->content.$content;

        return $this;
    }

    /**
     * @return $this
     */
    public function prependContent($content): self
    {
        $this->content = $content.$this->content;

        return $this;
    }

    /**
     * Enable self close tag.
     *
     * @return $this
     */
    public function setSelfClose(bool $bool): self
    {
        $this->selfClose = $bool;

        return $this;
    }

    /**
     * Return html tag string.
     */
    public function build(): string
    {
        $html = '';
        $attrSting = '';
        if (count($this->attr) > 0) {
            foreach ($this->attr as $attr) {
                $attrSting .= $attr.'="'.$this->attrValues[$attr].'" ';
            }
        }
        if (false === $this->selfClose) {
            $html = '<'.$this->tag.' '.$attrSting.'>'.$this->content.'</'.$this->tag.'>';
        } else {
            $html = '<'.$this->tag.' '.$attrSting.'/>';
        }
        $html = str_replace('  ', ' ', $html);
        $html = str_replace('<'.$this->tag.' >', '<'.$this->tag.'>', $html);

        return $html;
    }
}
