<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class InputData {

    public function __construct() {
        if (isset($_SERVER['CONTENT_TYPE']) && strstr($_SERVER['CONTENT_TYPE'], "application/json")) {
            $input = file_get_contents('php://input');
            $json = json_decode($input, true);
            if (is_array($json)) {
                $_REQUEST = array_merge($_REQUEST, $json);
                if ($this->getRequestMethod() == "GET") {
                    $_GET = array_merge($_GET, $json);
                } elseif ($this->getRequestMethod() == "POST") {
                    $_POST = array_merge($_POST, $json);
                }
            }
        }
    }

    public function get(string $name = "", string $normalize = "", $default = null) {
        if ($name === "") {
            return $_GET;
        }
        if (isset($_GET[$name]) && $_GET[$name] !== "") {
            if (!empty($normalize)) {
                $normalizer = new \HemiFrame\Lib\Normalizer();
                $normalizer->setData($_GET[$name]);
                $normalizer->normalize($normalize);
                return $normalizer->getData();
            }
            return $_GET[$name];
        }
        return $default;
    }

    public function post(string $name = "", string $normalize = "", $default = null) {
        if ($name === "") {
            return $_POST;
        }
        if (isset($_POST[$name]) && $_POST[$name] !== "") {
            if (!empty($normalize)) {
                $normalizer = new \HemiFrame\Lib\Normalizer();
                $normalizer->setData($_POST[$name]);
                $normalizer->normalize($normalize);
                return $normalizer->getData();
            }
            return $_POST[$name];
        }
        return $default;
    }

    public function request(string $name = "", string $normalize = "", $default = null) {
        if ($name === "") {
            return $_REQUEST;
        }
        if (isset($_REQUEST[$name]) && $_REQUEST[$name] !== "") {
            if (!empty($normalize)) {
                $normalizer = new \HemiFrame\Lib\Normalizer();
                $normalizer->setData($_REQUEST[$name]);
                $normalizer->normalize($normalize);
                return $normalizer->getData();
            }
            return $_REQUEST[$name];
        }
        return $default;
    }

    public function files(string $name = "", $default = null) {
        if ($name === "") {
            return $_FILES;
        }
        if (isset($_FILES[$name])) {
            return $_FILES[$name];
        }
        return $default;
    }

    public function cookie(string $name = "", string $normalize = "", $default = null) {
        if ($name === "") {
            return $_COOKIE;
        }
        if (isset($_COOKIE[$name]) && $_COOKIE[$name] != "") {
            if (!empty($normalize)) {
                $normalizer = new \HemiFrame\Lib\Normalizer();
                $normalizer->setData($_COOKIE[$name]);
                $normalizer->normalize($normalize);
                return $normalizer->getData();
            }
            return $_COOKIE[$name];
        }
        return $default;
    }

    public function server(string $name = "", string $normalize = "", $default = null) {
        if ($name === "") {
            return $_SERVER;
        }
        if (isset($_SERVER[$name]) && $_SERVER[$name] != "") {
            if (!empty($normalize)) {
                $normalizer = new \HemiFrame\Lib\Normalizer();
                $normalizer->setData($_SERVER[$name]);
                $normalizer->normalize($normalize);
                return $normalizer->getData();
            }
            return $_SERVER[$name];
        }
        return $default;
    }

    public function getRequestMethod() {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            return $_SERVER['REQUEST_METHOD'];
        }
        return null;
    }

    public function getRequestUri() {
        if (isset($_SERVER['REQUEST_URI'])) {
            return $_SERVER['REQUEST_URI'];
        }
        return null;
    }

    public function getServerName() {
        if (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return null;
    }

    public function getQueryString() {
        if (isset($_SERVER['QUERY_STRING'])) {
            return $_SERVER['QUERY_STRING'];
        }
        return null;
    }

    public function getHttpReferer() {
        if (isset($_SERVER['HTTP_REFERER'])) {
            return $_SERVER['HTTP_REFERER'];
        }
        return null;
    }

    public function getHttpUserAgent() {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            return $_SERVER['HTTP_USER_AGENT'];
        }
        return null;
    }

    public function getHttpHost() {
        if (isset($_SERVER['HTTP_HOST'])) {
            return $_SERVER['HTTP_HOST'];
        }
        return null;
    }

    public function getIp() {
        foreach (array('HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_X_CLUSTER_CLIENT_IP', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR') as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    if (filter_var($ip, FILTER_VALIDATE_IP) !== false) {
                        return $ip;
                    }
                }
            }
        }
    }

}
