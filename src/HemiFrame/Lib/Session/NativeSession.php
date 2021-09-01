<?php

namespace HemiFrame\Lib\Session;

/**
 * @author heminei <heminei@heminei.com>
 */
class NativeSession implements \HemiFrame\Interfaces\Session
{
    private $name = "PHPSESSID";
    private $lifeTime = 3600;
    private $cookiePath = "/";
    private $cookieDomain = "";
    private $cookieSecure = false;
    private $cookieHttpOnly = true;

    public function __construct($sessionName = "PHPSESSID")
    {
        $this->setName($sessionName);
    }

    public function __get(string $name)
    {
        return $this->get($name);
    }

    public function __set(string $name, $value)
    {
        $this->set($name, $value);
    }

    public function get(string $name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }

        return null;
    }

    public function set(string $name, $value): self
    {
        $_SESSION[$name] = $value;

        return $this;
    }

    public function start()
    {
        session_name($this->getName());
        ini_set("session.gc_maxlifetime", (string) $this->getLifeTime());
        session_set_cookie_params(
            $this->getLifeTime(),
            $this->getCookiePath(),
            $this->getCookieDomain(),
            $this->getCookieSecure(),
            $this->getCookieHttpOnly()
        );
        session_start();
    }

    public function destroy()
    {
        session_destroy();
    }

    public function getId(): string
    {
        return session_id();
    }

    public function save()
    {
        session_write_close();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLifeTime(): int
    {
        return $this->lifeTime;
    }

    public function getCookiePath(): string
    {
        return $this->cookiePath;
    }

    public function getCookieDomain(): string
    {
        return $this->cookieDomain;
    }

    public function getCookieSecure(): bool
    {
        return $this->cookieSecure;
    }

    public function getCookieHttpOnly(): bool
    {
        return $this->cookieHttpOnly;
    }

    public function setName(string $name): self
    {
        if (empty($name)) {
            throw new \RuntimeException("Session name can't be empty string");
        }
        $this->name = $name;

        return $this;
    }

    public function setLifeTime(int $lifeTime): self
    {
        $this->lifeTime = $lifeTime;

        return $this;
    }

    public function setCookiePath(string $cookiePath): self
    {
        $this->cookiePath = $cookiePath;

        return $this;
    }

    public function setCookieDomain(string $cookieDomain): self
    {
        $this->cookieDomain = $cookieDomain;

        return $this;
    }

    public function setCookieSecure(bool $cookieSecure): self
    {
        $this->cookieSecure = $cookieSecure;

        return $this;
    }

    public function setCookieHttpOnly(bool $cookieHttpOnly): self
    {
        $this->cookieHttpOnly = $cookieHttpOnly;

        return $this;
    }
}
