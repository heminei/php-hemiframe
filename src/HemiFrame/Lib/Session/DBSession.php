<?php

namespace HemiFrame\Lib\Session;

/**
 * @author heminei <heminei@heminei.com>
 */
class DBSession implements \HemiFrame\Interfaces\Session
{
    private $id = '';
    private $name = 'PHPSESSID';
    private $lifeTime = 3600;
    private $cookiePath = '/';
    private $cookieDomain = '';
    private $cookieSecure = false;
    private $cookieHttpOnly = true;
    private $pdo;
    private $tableName = 'sessions';
    private $data = [];

    public function __construct($sessionName = 'PHPSESSID', $pdo = null)
    {
        if (strlen($sessionName) > 1) {
            $this->setName($sessionName);
        }
        if ($pdo instanceof \PDO) {
            $this->setPdo($pdo);
        }
        if (isset($_COOKIE[$sessionName])) {
            $this->id = $_COOKIE[$sessionName];
        }
    }

    public function start()
    {
        if (!$this->pdo instanceof \PDO) {
            throw new \Exception('Set PDO object!');
        }
        if ('' == $this->getId()) {
            $this->startNew();
        } elseif (false === $this->validate()) {
            $this->startNew();
        }

        setcookie(
            $this->getName(),
            $this->getId(),
            time() + $this->getLifeTime(),
            $this->getCookiePath(),
            $this->getCookieDomain(),
            $this->getCookieSecure(),
            $this->getCookieHttpOnly()
        );
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
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        return null;
    }

    public function set(string $name, $value): self
    {
        $this->data[$name] = $value;

        return $this;
    }

    public function destroy(): self
    {
        if (null != $this->id) {
            $stm = $this->getPdo()->prepare('DELETE FROM '.$this->getTableName().' WHERE sessionId=:sessionId AND `name`=:name');
            $stm->bindValue(':sessionId', $this->getId());
            $stm->bindValue(':name', $this->getName());
            $stm->execute();
        }

        return $this;
    }

    public function deleteExpired()
    {
        if (!$this->pdo instanceof \PDO) {
            throw new \RuntimeException('Set PDO object!');
        }

        $stm = $this->getPdo()->prepare('DELETE FROM '.$this->getTableName().' WHERE expiryDate < :date');
        $stm->bindValue(':date', date('Y-m-d H:i:s'));
        $stm->execute();
    }

    public function save(): self
    {
        if (null != $this->id) {
            $stm = $this->getPdo()->prepare('UPDATE '.$this->getTableName().' SET `data`=:data, `expiryDate`=:expiryDate 
            WHERE sessionId=:sessionId');
            $stm->bindValue(':data', serialize($this->data));
            $stm->bindValue(':expiryDate', date('Y-m-d H:i:s', time() + $this->getLifeTime()));
            $stm->bindValue(':sessionId', $this->getId());
            $stm->execute();
        }

        return $this;
    }

    public function getId(): string
    {
        return $this->id;
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

    public function getPdo(): ?\PDO
    {
        return $this->pdo;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function setName(string $name): self
    {
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

    public function setPdo(\PDO $pdo): self
    {
        $this->pdo = $pdo;

        return $this;
    }

    public function setTableName(string $tableName): self
    {
        $this->tableName = $tableName;

        return $this;
    }

    private function startNew()
    {
        $this->id = md5(uniqid(__DIR__, true));

        $stm = $this->getPdo()->prepare('INSERT INTO '.$this->getTableName().' SET sessionId=:sessionId, `name`=:name, `expiryDate`= :expiryDate');
        $stm->bindValue(':sessionId', $this->getId());
        $stm->bindValue(':name', $this->getName());
        $stm->bindValue(':expiryDate', date('Y-m-d H:i:s', time() + $this->getLifeTime()));
        $stm->execute();

        setcookie(
            $this->getName(),
            $this->getId(),
            time() + $this->getLifeTime(),
            $this->getCookiePath(),
            $this->getCookieDomain(),
            $this->getCookieSecure(),
            $this->getCookieHttpOnly()
        );
    }

    private function validate(): bool
    {
        if (null != $this->getId()) {
            $stm = $this->getPdo()->prepare('SELECT * FROM '.$this->getTableName().' WHERE sessionId=:sessionId AND `name`=:name AND `expiryDate`>= :expiryDate');
            $stm->bindValue(':sessionId', $this->getId());
            $stm->bindValue(':name', $this->getName());
            $stm->bindValue(':expiryDate', date('Y-m-d H:i:s'));
            $stm->execute();
            $rows = $stm->fetchAll(\PDO::FETCH_OBJ);

            if (1 == count($rows)) {
                $this->data = unserialize($rows[0]->data);

                return true;
            }
        }

        return false;
    }
}
