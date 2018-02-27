<?php

namespace HemiFrame\Lib;

class MetaTags {

	private $tags = array();

	public function __construct() {

	}

	public function __toString() {
		return $this->parse();
	}

	public function setTag(string $name, string $content): self {
		if ($name != NULL) {
			$this->tags[$name] = $content;
		}
		return $this;
	}

	public function getTag(string $name) {
		if (isset($this->tags[$name])) {
			return $this->tags[$name];
		}
		return null;
	}

	public function getTags(): array {
		return $this->tags;
	}

	public function parse(): string {
		$html = "";
		foreach ($this->tags AS $key => $value) {
			$html .= '<meta name="' . $key . '" content="' . $value . '">
';
		}
		return $html;
	}

}
