<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Url
{
    private $url = null;
    private $scheme = null;
    private $host = null;
    private $port = null;
    private $user = null;
    private $pass = null;
    private $path = null;
    private $query = null;
    private $fragment = null;

    public function __construct($url = null)
    {
        if ($url !== null) {
            $this->setUrl($url);
        }
    }

    public function __toString()
    {
        return $this->getUrl();
    }

    public function parseUrl()
    {
        $parseUrl = parse_url($this->url);

        $this->scheme = isset($parseUrl['scheme']) ? $parseUrl['scheme'] : null;
        $this->host = isset($parseUrl['host']) ? $parseUrl['host'] : null;
        $this->port = isset($parseUrl['port']) ? $parseUrl['port'] : null;
        $this->user = isset($parseUrl['user']) ? $parseUrl['user'] : null;
        $this->pass = isset($parseUrl['pass']) ? $parseUrl['pass'] : null;
        $this->path = isset($parseUrl['path']) ? $parseUrl['path'] : null;
        $this->query = isset($parseUrl['query']) ? $parseUrl['query'] : null;
        $this->fragment = isset($parseUrl['fragment']) ? $parseUrl['fragment'] : null;

        return $parseUrl;
    }

    public function parseStr()
    {
        $array = [];
        parse_str($this->query, $array);
        return $array;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;
        $this->parseUrl();
        return $this;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getScheme()
    {
        return $this->scheme;
    }

    public function getHost()
    {
        return $this->host;
    }

    public function getPort()
    {
        return $this->port;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getPass()
    {
        return $this->pass;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFragment()
    {
        return $this->fragment;
    }
}
