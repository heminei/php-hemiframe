<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Router
{

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
    private $cache = null;
    /**
     * @var array
     */
    private $patterns = [
        "vars" => "/\{\{(?<name>[a-zA-Z0-9]+)\}\}/i",
        "lang" => "(?<lang>[a-z0-9]{1,2})?",
    ];

    public function __construct()
    {
        if (isset($_SERVER['HTTP_HOST'])) {
            $this->host = $_SERVER['HTTP_HOST'];
        }

        $this->resetCurrentRoute();
    }

    public function getRequestUri(): string
    {
        return $this->requestUri;
    }

    public function setRequestUri(string $requestUri): self
    {
        $requestUri = explode("?", $requestUri);
        $requestUri = $requestUri[0];
        $requestUri = urldecode($requestUri);
        $requestUri = iconv(mb_detect_encoding($requestUri, mb_detect_order(), true), "UTF-8", $requestUri);
        $this->requestUri = $requestUri;

        return $this;
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function setBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getDefaultClass()
    {
        return $this->defaultClass;
    }

    public function getDefaultMethod()
    {
        return $this->defaultMethod;
    }

    public function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
    }

    public function setDefaultMethod($defaultMethod)
    {
        $this->defaultMethod = $defaultMethod;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    public function getCurrentRoute(): array
    {
        return $this->currentRoute;
    }

    /**
     * Get regex patterns
     * @return array
     */
    public function getPatterns(): array
    {
        return $this->patterns;
    }

    public function setPattern(string $key, string $value): self
    {
        if (!array_key_exists($key, $this->patterns)) {
            throw new \InvalidArgumentException("Invalid pattern key: " . $key);
        }

        $this->patterns[$key] = $value;

        return $this;
    }

    /**
     * @return \HemiFrame\Interfaces\Cache|null
     */
    public function getCache(): ?\HemiFrame\Interfaces\Cache
    {
        return $this->cache;
    }

    /**
     * @param \HemiFrame\Interfaces\Cache|null $cache
     * @return self
     */
    public function setCache(?\HemiFrame\Interfaces\Cache $cache): self
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * @param string $path
     * @param integer $cacheTime
     * @param string"null $cacheKey
     * @return array
     */
    public function scanDirectory(string $path, int $cacheTime = 300, ?string $cacheKey = null): array
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Patch is not readable: " . $path);
        }
        if ($cacheKey === null) {
            $cacheKey = "router-directory-scan-" . md5($path . __FILE__);
        }
        $isCached = false;
        $routes = [];
        if (!empty($this->cache) && $cacheTime > 0) {
            if ($this->cache->exists($cacheKey) && !empty($this->cache->get($cacheKey))) {
                $isCached = true;
                $routes = $this->cache->get($cacheKey);
            }
        }

        if ($isCached == false) {
            $allFiles = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path));
            $files = new \RegexIterator($allFiles, '/\.php$/');

            $classes = [];
            foreach ($files as $file) {
                /** @var \SplFileInfo $file */
                $tokens = token_get_all(file_get_contents($file->getRealPath()));
                $namespace = "\\";

                foreach ($tokens as $key => $token) {
                    if (T_NAMESPACE === $token[0]) {
                        $index = $key + 2; // Skip namespace keyword and whitespace
                        while (isset($tokens[$index]) && is_array($tokens[$index])) {
                            $namespace .= $tokens[$index++][1];
                        }
                    }
                    if (T_CLASS === $tokens[$key][0] && T_WHITESPACE === $tokens[$key + 1][0] && T_STRING === $tokens[$key + 2][0]) {
                        $classes[] = $namespace . '\\' . $tokens[$key + 2][1];
                    }
                }
            }

            foreach ($classes as $class) {
                $annotations = [];
                $rc = new \ReflectionClass($class);
                if (!empty($rc->getDocComment())) {
                    $annotations[] = [
                        "comment" => $rc->getDocComment(),
                        "method" => null,
                    ];
                }
                foreach ($rc->getMethods() as $method) {
                    if (!empty($method->getDocComment())) {
                        $annotations[] = [
                            "comment" => $method->getDocComment(),
                            "method" => $method->getName(),
                        ];
                    }
                }

                foreach ($annotations as $annotation) {
                    $lines = explode("\n", $annotation['comment']);
                    foreach ($lines as $line) {
                        if (strstr($line, "@Route({")) {
                            $stringArray = explode("@Route({", $line);
                            $json = trim("{" . $stringArray[1]);
                            $json = rtrim($json, "})") . "}";

                            $array = json_decode($json, true);
                            $jsonError = \json_last_error_msg();

                            if ($jsonError != "No error" || !is_array($array)) {
                                throw new \RuntimeException("Invalid @Route annotation on class " . $class . ": " . $json);
                            }

                            if (!isset($array['controller'])) {
                                $array['controller'] = $class;
                            }
                            if (!isset($array['method']) && isset($annotation['method'])) {
                                $array['method'] = $annotation['method'];
                            }
                            if (!isset($array['key'])) {
                                throw new \InvalidArgumentException("Enter @Route key annotation on class " . $class . ": " . $json);
                            }
                            if (!isset($array['url'])) {
                                throw new \InvalidArgumentException("Enter @Route url annotation on class " . $class . ": " . $json);
                            }

                            $routes[] = [
                                "class" => $class,
                                "settings" => $array,
                            ];
                        }
                    }
                }
            }
        }

        foreach ($routes as $route) {
            $this->setRoute($route['settings']);
        }

        if (!empty($this->cache) && $cacheTime > 0) {
            $this->cache->set($cacheKey, $routes, $cacheTime);
        }

        return $routes;
    }

    /**
     * @param string $path
     * @param string|null $cacheKey
     * @return boolean
     */
    public function clearScanDirectoryCache(string $path, ?string $cacheKey = null): bool
    {
        if (!is_readable($path)) {
            throw new \InvalidArgumentException("Path is not readable: " . $path);
        }
        if (empty($this->cache)) {
            return false;
        }

        if ($cacheKey === null) {
            $cacheKey = "router-directory-scan-" . md5($path . __FILE__);
        }

        return  $this->cache->delete($cacheKey);
    }

    /**
     *
     * @param array $array
     * @return \self
     * @throws \InvalidArgumentException
     */
    public function setRoute(array $array): self
    {
        if (!isset($array['key'])) {
            throw new \InvalidArgumentException("Enter key");
        }
        if (!isset($array['url'])) {
            throw new \InvalidArgumentException("Enter url");
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
        foreach ($vars['name'] as $row) {
            $this->urlVars[$key][] = $row;
        }

        return $this;
    }

    /**
     * @param string|array $array
     * @return string
     */
    public function getRoute($array): string
    {
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

    public function setRedirect(array $array): self
    {
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
        foreach ($vars['name'] as $row) {
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
    public function match(): array
    {
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
            $urlPreg = preg_replace("/\{\{([a-zA-Z0-9\-\_а-яА-Я]+)\|number\}\}/i", "(?<$1>[0-9]+)", $urlPreg);
            $urlPreg = preg_replace("/\{\{([a-zA-Z0-9\-\_а-яА-Я]+)\}\}/i", "(?<$1>[a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s\%\+'\",]+)", $urlPreg);
            $urlPreg = str_replace("/", "\/", $urlPreg);

            $matches = [];
            if (!preg_match("/^\/?" . $this->patterns['lang'] . "$urlPreg\/?$/i", $url, $matches)) {
                continue;
            }
            if ($this->urlHosts[$key] != $this->host) {
                continue;
            }

            $this->currentRoute["key"] = $key;

            if (isset($matches['lang'])) {
                $this->currentRoute["lang"] = $matches['lang'];
            }
            if (isset($this->urlVars[$key]) && is_array($this->urlVars[$key])) {
                $this->currentRoute["vars"] = [];
                foreach ($this->urlVars[$key] as $varKey => $var) {
                    $this->currentRoute["vars"][$var] = isset($matches[$var]) ? $matches[$var] : null;
                }
            }

            $this->currentRoute["method"] = null;
            if (isset($this->urlMethods[$key])) {
                $this->currentRoute["method"] = $this->replaceVars($this->currentRoute["vars"], $this->urlMethods[$key]);
            }

            $this->currentRoute["class"] = $this->urlControllers[$key];
            break;
        }

        if ($this->currentRoute["class"] === NULL && $this->currentRoute["method"] === NULL) {
            foreach ($this->urlRedirects as $redirect) {
                $urlPreg = preg_replace("/\{\{([a-zA-Z0-9\-\_а-яА-Я]+)\|number\}\}/i", "(?<$1>[0-9]+)", $urlPreg);
                $urlPreg = preg_replace("/\{\{([a-zA-Z0-9\-\_а-яА-Я]+)\}\}/i", "([a-zA-Zа-яА-Я0-9абвгдежзийклмнопрстуфхцчшщъьюя=\.@_:\[\]\-\s]+)", $redirect['fromUrl']);
                $urlPreg = str_replace("/", "\/", $urlPreg);

                $matches = [];
                if (preg_match("/^\/?" . $this->patterns['lang'] . "$urlPreg\/?$/i", $url, $matches)) {

                    if ($redirect['toUrl'] != NULL) {
                        $redirectToUrl = $redirect['toUrl'];
                    } else {
                        $redirectToUrl = $this->getRoute($redirect['toUrlKey']);
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

    private function replaceVars(array $vars, string $string)
    {
        foreach ($vars as $k => $v) {
            $string = str_replace("{{" . $k . "}}", $v, $string);
        }
        return $string;
    }

    private function resetCurrentRoute()
    {
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
