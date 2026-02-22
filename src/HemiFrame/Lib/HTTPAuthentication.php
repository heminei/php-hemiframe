<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class HTTPAuthentication
{
    /**
     * @var array
     */
    private $users = [];

    /**
     * @var string|null
     */
    private $message;

    public function __construct(?string $message = null)
    {
        if (null !== $message) {
            $this->setMessage($message);
        } else {
            $this->setMessage('Access restricted');
        }
    }

    /**
     * @return bool
     */
    public function login()
    {
        if (isset($_SERVER['PHP_AUTH_USER']) and (array_key_exists($_SERVER['PHP_AUTH_USER'], $this->getUsers())
            and $_SERVER['PHP_AUTH_PW'] == $this->users[$_SERVER['PHP_AUTH_USER']])) {
            return true;
        }
        header('WWW-Authenticate: Basic realm="'.$this->getMessage().'"');
        header('HTTP/1.0 401 Unauthorized');

        return false;
    }

    public function addUser(string $user, string $password): self
    {
        $this->users[$user] = $password;

        return $this;
    }

    public function getUsers(): array
    {
        return $this->users;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return $this
     */
    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }
}
