<?php

namespace HemiFrame\Lib;

class BBCode {

	private $text = NULL;

	public function __construct($text) {
		$this->text = $text;
	}

	public function __toString() {
		return $this->parse();
	}

	public function setText($text) {
		$this->text = $text;
	}

	public function getText() {
		return $this->text;
	}

	private function autoLink() {
		$reg_exUrl = "/(?<!=\"|='|\">|'>)((((http|https|ftp|ftps)\:\/\/))[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,4}(\/\S*)?)/";
		$this->text = preg_replace($reg_exUrl, "<a href=\"$1\">$1</a>", $this->text);
		return $this->text;
	}

	private function setVideoFromVbox() {
		$matches = $this->getBetween($this->text, "[vbox]", "[/vbox]");
		if ($matches != NULL) {
			$vid = explode(":", $matches);
			$vid = end($vid);
			$html = "<div class='embed-responsive embed-responsive-16by9'>";
			$html .= "<iframe class='embed-responsive-item' src='http://vbox7.com/emb/external.php?vid=$vid' frameborder='0' allowfullscreen></iframe>";
			$html .= "</div>";

			$this->text = str_replace("[vbox]" . "$matches" . "[/vbox]", $html, $this->text);
		}
	}

	private function setVideoFromYoutube() {
		$matches = $this->getBetween($this->text, "[youtube]", "[/youtube]");
		if ($matches != NULL) {
			$videoUrl = new \HemiFrame\Lib\Url($matches);
			if (strstr($videoUrl->getUrl(), "youtube.com")) {
				$vid = $videoUrl->parseStr()['v'];
				$html = "<div class='embed-responsive embed-responsive-16by9'>";
				$html .= "<iframe class='embed-responsive-item' src='//www.youtube.com/embed/$vid' frameborder='0' allowfullscreen></iframe>";
				$html .= "</div>";
				$this->text = str_replace("[youtube]$matches" . "[/youtube]", $html, $this->text);
			}
		}
	}

	private function getBetween($content, $start, $end) {
		$r = explode($start, $content);
		if (isset($r[1])) {
			$r = explode($end, $r[1]);
			return $r[0];
		}
		return '';
	}

	public function parse() {
		$this->text = stripcslashes(html_entity_decode($this->text));
		$this->text = preg_replace("/\[url\](.+?)\[\/url\]/s", '<a href="$1">$1</a>', $this->text);
		$this->text = preg_replace("/\[url\=(.+?)\](.+?)\[\/url\]/s", '<a href="$1">$2</a>', $this->text);
		$this->text = preg_replace("/\[b\](.+?)\[\/b\]/s", '<strong>$1</strong>', $this->text);
		$this->text = preg_replace("/\[u\](.+?)\[\/u\]/s", '<span style="text-decoration: underline;">$1</span>', $this->text);
		$this->text = preg_replace("/\[i\](.+?)\[\/i\]/s", '<span style="font-style: italic;">$1</span>', $this->text);
		$this->text = preg_replace("/\[s\](.+?)\[\/s\]/s", '<s>$1</s>', $this->text);
		$this->text = preg_replace("/\[del\](.+?)\[\/del\]/s", '<del>$1</del>', $this->text);
		$this->text = preg_replace("/\[code\](.+?)\[\/code\]/s", '<div class="codeTitle">КОД:</div><div class="codeContent">$1</div>', $this->text);
		$this->text = preg_replace("/\[quote\](.+?)\[\/quote\]/s", '<div class="quoteTitle">Цитат:</div><div class="quoteContent">$1</div><br>', $this->text);
		$this->text = preg_replace("/\[img\](.+?)\[\/img\]/s", '<img style="max-width: 100%;" src="$1" alt="">', $this->text);
		$this->text = preg_replace("/\[color=(.+?)\](.+?)\[\/color\]/s", '<span style="color: $1;">$2</span>', $this->text);
		$this->text = preg_replace("/\[blink\](.+?)\[\/blink\]/s", '<blink>$1</blink>', $this->text);
		$this->text = preg_replace("/\[center\](.+?)\[\/center\]/s", '<p style="text-align: center;">$1</p>', $this->text);
		$this->text = preg_replace("/\[left\](.+?)\[\/left\]/s", '<p style="text-align: left;">$1</p>', $this->text);
		$this->text = preg_replace("/\[right\](.+?)\[\/right\]/s", '<p style="text-align: right;">$1</p>', $this->text);

		$this->setVideoFromVbox();
		$this->setVideoFromYoutube();
		$this->autoLink();

		return $this->text;
	}

}
