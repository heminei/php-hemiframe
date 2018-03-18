<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Router {

    private $requestUri = "";
    private $currentRoute = [];
    private $basePath = null;
    private $host = null;
    private $defaultClass = null;
    private $defaultMethod = null;
    private $lang = "";
    private $urlArray = [];
    private $urlVars = [];
    private $urlControllers = [];
    private $urlMethods = [];
    private $urlHosts = [];
    private $urlLangs = [];
    private $urlRedirects = [];
    private $patterns = [
        "vars" => "/\{\{(?<name>[a-zA-Z0-9]+)\}\}/i"
    ];

    public function __construct() {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->host = $_SERVER['HTTP_HOST'];
        }

        $this->resetCurrentRoute();
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

    public function getDefaultClass() {
        return $this->defaultClass;
    }

    public function getDefaultMethod() {
        return $this->defaultMethod;
    }

    public function setDefaultClass($defaultClass) {
        $this->defaultClass = $defaultClass;
    }

    public function setDefaultMethod($defaultMethod) {
        $this->defaultMethod = $defaultMethod;
    }

    public function getLang(): string {
        return $this->lang;
    }

    public function setLang(string $lang): self {
        $this->lang = $lang;

        return $this;
    }

    public function getCurrentRoute(): array {
        return $this->currentRoute;
    }

    /**
     *
     * @param array $array
     * @return \self
     * @throws \InvalidArgumentException
     */
    public function setRoute(array $array): self {
        if (!isset($array['key'])) {
            throw new \InvalidArgumentException("Enter key");
        }
        if (!isset($array['url'])) {
            throw new \InvalidArgumentException("Enter URL");
        }
        if (!isset($array['controller'])) {
            throw new \InvalidArgumentException("Enter controller");
        }
        $key = $array['key'];
        $url = $array['url'];
        $class = $array['controller'];

        if (isset($array['lang'])) {
            $lang = $array['lang'];
            $key = $lang . "." . $key;
        } else {
            $lang = NULL;
        }
        if (array_key_exists($key, $this->urlArray)) {
            throw new \InvalidArgumentException("$key - key exists");
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

    /**
     *
     * @param type $array
     * @return string
     */
    public function getRoute($array): string {
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
            throw new \InvalidArgumentException("Enter fromUrl");
        }
        if (!isset($array['toUrl']) && !isset($array['toUrlKey'])) {
            throw new \InvalidArgumentException("Enter toUrl or toUrlKey");
        }
        if (!isset($array['toUrl'])) {
            $array['toUrl'] = NULL;
            if (!array_key_exists($array['toUrlKey'], $this->urlArray)) {
                throw new \InvalidArgumentException("To URL key " . $array['toUrlKey'] . " not found.");
            }
        }
        if (!isset($array['toUrlKey'])) {
            $array['toUrlKey'] = NULL;
        }

        $vars = [];
        $varNames = [];
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

    /**
     *
     * @return array
     */
    public function match(): array {
        $this->resetCurrentRoute();

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

        foreach ($this->urlArray as $key => $urlPreg) {
            $urlPreg = preg_replace("/\{\{(.*?)\}\}/i", "([a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s]+)", $urlPreg);
            $urlPreg = str_replace("/", "\/", $urlPreg);

            $find = "/^\/?([a-z0-9]{1,3})?$urlPreg\/?$/i";
            $matches = [];
            if (preg_match($find, $url, $matches)) {
                if ($this->urlHosts[$key] == $this->host) {
                    $this->currentRoute["key"] = $key;

                    if (isset($matches[1])) {
                        $this->currentRoute["lang"] = $matches[1];
                    }
                    if (isset($this->urlVars[$key])) {
                        if (is_array($this->urlVars[$key])) {
                            $this->currentRoute["vars"] = [];
                            foreach ($this->urlVars[$key] as $varKey => $var) {
                                $this->currentRoute["vars"][$var] = $matches[$varKey + 2];
                            }
                        }
                    }
                    if (isset($this->urlMethods[$key])) {
                        $this->currentRoute["method"] = $this->replaceVars($this->currentRoute["vars"], $this->urlMethods[$key]);
                    } else {
                        $this->currentRoute["method"] = null;
                    }

                    $this->currentRoute["class"] = $this->urlControllers[$key];
                    break;
                }
            }
        }

        if ($this->currentRoute["class"] === NULL && $this->currentRoute["method"] === NULL) {
            foreach ($this->urlRedirects as $redirectKey => $redirect) {
                $urlPreg = preg_replace("/\{\{(.*?)\}\}/i", "([a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s]+)", $redirect['fromUrl']);
                $urlPreg = str_replace("/", "\/", $urlPreg);

                $find = "/^\/?([a-z0-9]{1,3})?$urlPreg\/?$/i";
                $matches = [];
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
                    http_response_code($redirect['statusCode']);
                    header("Location: $redirectToUrl");
                    exit();
                }
            }
        }

        if (empty($this->currentRoute["class"])) {
            $this->currentRoute["class"] = $this->getDefaultClass();
        }
        if (empty($this->currentRoute["method"])) {
            $this->currentRoute["method"] = $this->getDefaultMethod();
        }

        if (!empty($this->currentRoute["class"])) {
            $this->currentRoute["class"] = $this->replaceVars($this->currentRoute["vars"], $this->currentRoute["class"]);
        }

        return $this->currentRoute;
    }

    private function replaceVars(array $vars, string $string) {
        foreach ($vars as $k => $v) {
            $string = str_replace("{{" . $k . "}}", $v, $string);
        }
        return $string;
    }

    private function resetCurrentRoute() {
        $this->currentRoute = [
            "key" => null,
            "class" => null,
            "method" => null,
            "vars" => [],
            "lang" => null
        ];

        return $this;
    }

}
