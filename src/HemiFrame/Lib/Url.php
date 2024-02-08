<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Url
{
    private $url;
    private $scheme;
    private $host;
    private $port;
    private $user;
    private $pass;
    private $path;
    private $query;
    private $fragment;

    public function __construct($url = null)
    {
        if (null !== $url) {
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
        $this->port = isset($parseUrl['port']) ? (int) $parseUrl['port'] : null;
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

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getPort(): ?int
    {
        return $this->port;
    }

    public function getUser(): ?string
    {
        return $this->user;
    }

    public function getPass(): ?string
    {
        return $this->pass;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function getQuery(): ?string
    {
        return $this->query;
    }

    public function getFragment(): ?string
    {
        return $this->fragment;
    }
}
