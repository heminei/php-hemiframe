<?php

namespace HemiFrame\Lib;

/**
 * Create tag
 * @author Heminei
 */
class Tag
{

	private $tag;
	private $attr = array();
	private $attrValues = array();
	private $content = NULL;
	private $selfClose = FALSE;
	private $selfCloseTagList = array("img", "link", "meta", "br", "hr", "input");

	public function __construct($tag = NULL)
	{
		if ($tag !== NULL) {
			$this->setTag($tag);
		}
	}

	public function __toString()
	{
		return $this->build();
	}

	/**
	 * Get string
	 * @return string
	 */
	public function getTag(): string
	{
		return $this->tag;
	}

	/**
	 * Set string
	 * @param string $tag
	 * @return $this
	 */
	public function setTag(string $tag): self
	{
		$this->tag = $tag;
		if (in_array($this->tag, $this->selfCloseTagList)) {
			$this->setSelfClose(TRUE);
		}

		return $this;
	}

	/**
	 * Get attr value
	 * @param string $attr
	 * @return string
	 */
	public function getAttr(string $attr): string
	{
		if (isset($this->attrValues[$attr])) {
			return $this->attrValues[$attr];
		} else {
			return "";
		}
	}

	/**
	 * Set attr
	 * @param string $attr
	 * @param mixed $value
	 * @return $this
	 */
	public function setAttr(string $attr, $value): self
	{
		$this->attr[] = $attr;
		$this->attrValues[$attr] = htmlentities($value);
		return $this;
	}

	/**
	 * Get inner content
	 * @return mixed
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set inner content
	 * @param mixed $content
	 * @return $this
	 */
	public function setContent($content): self
	{
		$this->content = $content;

		return $this;
	}

	/**
	 *
	 * @param mixed $content
	 * @return $this
	 */
	public function appendContent($content): self
	{
		$this->content = $this->content . $content;

		return $this;
	}

	/**
	 *
	 * @param mixed $content
	 * @return $this
	 */
	public function prependContent($content): self
	{
		$this->content = $content . $this->content;

		return $this;
	}

	/**
	 * Enable self close tag
	 * @param bool $bool
	 * @return $this
	 */
	public function setSelfClose(bool $bool): self
	{
		$this->selfClose = $bool;

		return $this;
	}

	/**
	 * Return html tag string
	 * @return string
	 */
	public function build(): string
	{
		$html = "";
		$attrSting = "";
		if (count($this->attr) > 0) {
			foreach ($this->attr as $attr) {
				$attrSting .= $attr . "=\"" . $this->attrValues[$attr] . "\" ";
			}
		}
		if ($this->selfClose === FALSE) {
			$html = "<" . $this->tag . " " . $attrSting . ">" . $this->content . "</" . $this->tag . ">";
		} else {
			$html = "<" . $this->tag . " " . $attrSting . "/>";
		}
		$html = str_replace("  ", " ", $html);
		$html = str_replace("<" . $this->tag . " >", "<" . $this->tag . ">", $html);

		return $html;
	}
}
