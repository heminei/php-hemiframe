<?php

namespace HemiFrame\Lib;

class OgTags {

	private $tags = array();

	public function __construct() {

	}

	public function __toString() {
		return $this->parse();
	}

	public function setTag($property, $content) {
		if ($property != NULL) {
			$this->tags[$property] = $content;
		}
		return $this;
	}

	public function getTag($property) {
		if (isset($this->tags[$property])) {
			return $this->tags[$property];
		}
		return false;
	}

	public function getTags() {
		return $this->tags;
	}

	public function parse() {
		$html = "";

		foreach ($this->tags AS $key => $value) {
			if (strstr($key, "fb:") OR strstr($key, "og:")) {
				$html .= '<meta property="' . $key . '" content="' . htmlentities($value) . '">
		';
			} else {
				$html .= '<meta property="og:' . $key . '" content="' . htmlentities($value) . '">
		';
			}
		}

		return $html;
	}

}
