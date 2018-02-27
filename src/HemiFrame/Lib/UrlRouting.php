<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class UrlRouting {

	private $requestUri = "";
	private $controller = array();
	private $basePath = NULL;
	private $host = NULL;
	private $lang = "";
	private $urlArray = array();
	private $urlVars = array();
	private $urlControllers = array();
	private $urlMethods = array();
	private $urlHosts = array();
	private $urlLangs = array();
	private $urlRedirects = array();
	private $patterns = [
		"vars" => "/\{\{(?<name>[a-zA-Z0-9]+)\}\}/i"
	];

	public function __construct() {
		if (isset($_SERVER['HTTP_HOST'])) {
			$this->host = $_SERVER['HTTP_HOST'];
		}

		$this->controller = array(
			"key" => NULL,
			"class" => NULL,
			"method" => NULL,
			"vars" => NULL,
			"lang" => NULL
		);
	}

	public function getRequestUri(): string {
		return $this->requestUri;
	}

	public function setRequestUri(string $requestUri): self {
		$requestUri = explode("?", $requestUri);
		$requestUri = $requestUri[0];
		$requestUri = urldecode($requestUri);
		$requestUri = iconv(mb_detect_encoding($requestUri, mb_detect_order(), true), "UTF-8", $requestUri);
		$this->requestUri = $requestUri;

		return $this;
	}

	public function getBasePath() {
		return $this->basePath;
	}

	public function setBasePath(string $basePath): self {
		$this->basePath = $basePath;

		return $this;
	}

	public function setHost(string $host): self {
		$this->host = $host;

		return $this;
	}

	public function getHost(): string {
		return $this->host;
	}

	public function getLang(): string {
		return $this->lang;
	}

	public function setLang(string $lang): self {
		$this->lang = $lang;

		return $this;
	}

	public function getController(): array {
		return $this->controller;
	}

	public function setUrl(array $array): self {
		if (!isset($array['key'])) {
			throw new \Exception("Key not set!");
		}
		if (!isset($array['url'])) {
			throw new \Exception("URL not set!");
		}
		if (isset($array['controller'])) {
			$class = $array['controller'];
		} else {
			if (isset($array['page'])) {
				$class = $array['page'];
			} else {
				throw new \Exception("Controller not set!");
			}
		}
		$key = $array['key'];
		$url = $array['url'];

		if (isset($array['lang'])) {
			$lang = $array['lang'];
			$key = $lang . "." . $key;
		} else {
			$lang = NULL;
		}
		if (array_key_exists($key, $this->urlArray)) {
			throw new \Exception("$key - Key exists!!!");
		}

		$this->urlArray[$key] = $url;
		$this->urlControllers[$key] = $class;

		if (isset($array['host'])) {
			$this->urlHosts[$key] = $array['host'] . $this->host;
		} else {
			$this->urlHosts[$key] = $this->host;
		}

		$this->urlLangs[$key] = $lang;

		if (isset($array['method'])) {
			$this->urlMethods[$key] = $array['method'];
		}

		$find = "/\{\{(?<name>[a-zA-Z0-9]+)\}\}/i";
		$vars = null;
		preg_match_all($find, $url, $vars);
		foreach ($vars['name'] AS $row) {
			$this->urlVars[$key][] = $row;
		}

		return $this;
	}

	public function getUrl($array): string {
		$vars = null;
		if (is_array($array)) {
			$key = $array['key'];
			if (isset($array['vars'])) {
				$vars = $array['vars'];
			}
			if (isset($array['lang'])) {
				$lang = $array['lang'];
			} else {
				$lang = $this->getLang();
			}
			if ($lang != NULL) {
				if (isset($this->urlArray[$lang . "." . $key])) {
					$urlString = $this->urlArray[$lang . "." . $key];
				} else {
					$urlString = $this->urlArray[$key];
				}
				$urlString = "/" . $lang . $urlString;
			} else {
				$urlString = $this->urlArray[$key];
			}
		} else {
			$key = $array;

			if ($this->getLang() != NULL) {
				if (isset($this->urlArray[$this->getLang() . "." . $key])) {
					$urlString = $this->urlArray[$this->getLang() . "." . $key];
				} else {
					$urlString = $this->urlArray[$key];
				}
				$urlString = "/" . $this->getLang() . $urlString;
			} else {
				$urlString = $this->urlArray[$key];
			}
		}


		$scriptNamePos = strpos($this->getRequestUri(), $_SERVER['SCRIPT_NAME']);
		if ($scriptNamePos === 0) {
			$urlString = $_SERVER['SCRIPT_NAME'] . $urlString;
		}

		if (is_array($vars)) {
			foreach ($vars as $k => $v) {
				$urlString = str_replace("{{" . $k . "}}", urlencode($v), $urlString);
			}
		}
		return $this->getBasePath() . $urlString;
	}

	public function setRedirect(array $array): self {
		if (!isset($array['statusCode'])) {
			$array['statusCode'] = 301;
		}
		if (!isset($array['fromUrl'])) {
			throw new \Exception("Redirect: Not set fromUrl!");
		}
		if (!isset($array['toUrl']) && !isset($array['toUrlKey'])) {
			throw new \Exception("Redirect: Not set toUrl or toUrlKey!");
		}
		if (!isset($array['toUrl'])) {
			$array['toUrl'] = NULL;
			if (!array_key_exists($array['toUrlKey'], $this->urlArray)) {
				throw new \Exception("Key " . $array['toUrlKey'] . " not found.");
			}
		}
		if (!isset($array['toUrlKey'])) {
			$array['toUrlKey'] = NULL;
		}

		$vars = array();
		$varNames = array();
		preg_match_all($this->patterns['vars'], $array['fromUrl'], $vars);
		foreach ($vars['name'] AS $row) {
			$varNames[] = $row;
		}
		$this->urlRedirects[] = [
			"fromUrl" => $array['fromUrl'],
			"toUrl" => $array['toUrl'],
			"toUrlKey" => $array['toUrlKey'],
			"statusCode" => $array['statusCode'],
			"vars" => $varNames
		];

		return $this;
	}

	public function match(): array {
		$url = $this->getRequestUri();

		if ($this->getBasePath() !== NULL) {
			if (substr($url, 0, strlen($this->getBasePath())) == $this->getBasePath()) {
				$url = substr_replace($url, "", 0, strlen($this->getBasePath()));
			}
		}

		$scriptNamePos = strpos($url, $_SERVER['SCRIPT_NAME']);
		if ($scriptNamePos === 0) {
			$url = substr_replace($url, "", 0, strlen($_SERVER['SCRIPT_NAME']));
			if (strlen($url) === 0) {
				$url .= "/";
			}
		}
		$this->controller["class"] = NULL;
		foreach ($this->urlArray as $key => $urlPreg) {
			$urlPreg = preg_replace("/\{\{(.*?)\}\}/i", "([a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s]+)", $urlPreg);
			$urlPreg = str_replace("/", "\/", $urlPreg);

			$find = "/^\/?([a-z0-9]{1,3})?$urlPreg\/?$/i";
			$matches = array();
			if (preg_match($find, $url, $matches)) {
				if ($this->urlHosts[$key] == $this->host) {
					$this->controller["key"] = $key;

					if (isset($matches[1])) {
						$_GET["lang"] = $matches[1];
						$_REQUEST["lang"] = $matches[1];
						$this->controller["lang"] = $matches[1];
					}
					if (isset($this->urlVars[$key])) {
						if (is_array($this->urlVars[$key])) {
							$this->controller["vars"] = array();
							foreach ($this->urlVars[$key] as $varKey => $var) {
								$_GET[$var] = $matches[$varKey + 2];
								$_REQUEST[$var] = $matches[$varKey + 2];
								$this->controller["vars"][$var] = $matches[$varKey + 2];
							}
						}
					}
					if (isset($this->urlMethods[$key])) {
						$this->controller["method"] = $this->replaceVars($this->controller["vars"], $this->urlMethods[$key]);
					} else {
						$this->controller["method"] = null;
					}

					$this->controller["class"] = $this->urlControllers[$key];
					break;
				}
			}
		}

		if ($this->controller["class"] === NULL && $this->controller["method"] === NULL) {
			foreach ($this->urlRedirects as $redirectKey => $redirect) {
				$urlPreg = preg_replace("/\{\{(.*?)\}\}/i", "([a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s]+)", $redirect['fromUrl']);
				$urlPreg = str_replace("/", "\/", $urlPreg);

				$find = "/^\/?([a-z0-9]{1,3})?$urlPreg\/?$/i";
				$matches = array();
				if (preg_match($find, $url, $matches)) {

					if ($redirect['toUrl'] != NULL) {
						$redirectToUrl = $redirect['toUrl'];
					} else {
						$redirectToUrl = $this->getUrl($redirect['toUrlKey']);
					}
					foreach ($redirect['vars'] as $varKey => $var) {
						$redirectToUrl = str_replace("{{" . $var . "}}", $matches[$varKey + 2], $redirectToUrl);
					}
					header("Cache-Control: no-store, no-cache, must-revalidate");
					header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");
					$header = new \HemiFrame\Lib\Header();
					$header->setHeader($redirect['statusCode']);
					$header->send();
					header("Location: $redirectToUrl");
					exit();
				}
			}
		}

		if (is_array($this->controller["vars"])) {
			$this->controller["class"] = $this->replaceVars($this->controller["vars"], $this->controller["class"]);
		}

		return $this->controller;
	}

	private function replaceVars(array $vars, string $string) {
		foreach ($vars as $k => $v) {
			$string = str_replace("{{" . $k . "}}", $v, $string);
		}
		return $string;
	}

}
