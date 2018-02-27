<?php

namespace HemiFrame\Lib\Session;

/**
 * @author heminei <heminei@heminei.com>
 */
class DBSession implements \HemiFrame\Interfaces\Session {

	private $id = "";
	private $name = "PHPSESSID";
	private $lifeTime = 3600;
	private $cookiePath = "/";
	private $cookieDomain = "";
	private $cookieSecure = false;
	private $cookieHttpOnly = true;
	private $pdo = null;
	private $tableName = "sessions";
	private $data = array();

	public function __construct($sessionName = "PHPSESSID", $pdo = null) {
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

	public function start() {
		if (!$this->pdo instanceof \PDO) {
			throw new \Exception("Set PDO object!");
		}
		if ($this->getId() == "") {
			$this->startNew();
		} elseif ($this->validate() === FALSE) {
			$this->startNew();
		}

		setcookie($this->getName(), $this->getId(), time() + $this->getLifeTime()
			, $this->getCookiePath(), $this->getCookieDomain()
			, $this->getCookieSecure(), $this->getCookieHttpOnly());
	}

	public function __get(string $name) {
		return $this->get($name);
	}

	public function __set(string $name, $value) {
		return $this->set($name, $value);
	}

	public function get(string $name) {
		if (isset($this->data[$name])) {
			return $this->data[$name];
		} else {
			return NULL;
		}
	}

	public function set(string $name, $value): self {
		$this->data[$name] = $value;

		return $this;
	}

	public function destroy(): self {
		if ($this->id != NULL) {
			$q = new \HemiFrame\Lib\SQLBuilder\Query([
				"pdo" => $this->getPdo()
			]);
			$q->delete($this->getTableName())->where("name", $this->getName())->andWhere("sessionId", $this->getId());
			$q->execute();
		}

		return $this;
	}

	public function deleteExpired() {
		if (!$this->pdo instanceof \PDO) {
			throw new \Exception("Set PDO object!");
		}
		$q = new \HemiFrame\Lib\SQLBuilder\Query([
			"pdo" => $this->getPdo()
		]);
		$q->delete($this->getTableName())->where("name", $this->getName())->andWhere("expiryDate < :date");
		$q->setVar("date", date("Y-m-d H:i:s"))->execute();
	}

	public function save(): self {
		if ($this->id != NULL) {
			$q = new \HemiFrame\Lib\SQLBuilder\Query([
				"pdo" => $this->getPdo()
			]);
			$q->update($this->getTableName())->set([
				"data" => serialize($this->data),
				"expiryDate" => date("Y-m-d H:i:s", time() + $this->getLifeTime()),
			])->where("sessionId", $this->getId());
			$q->execute();
		}

		return $this;
	}

	public function getId(): string {
		return $this->id;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLifeTime(): int {
		return $this->lifeTime;
	}

	public function getCookiePath(): string {
		return $this->cookiePath;
	}

	public function getCookieDomain(): string {
		return $this->cookieDomain;
	}

	public function getCookieSecure(): bool {
		return $this->cookieSecure;
	}

	public function getCookieHttpOnly(): bool {
		return $this->cookieHttpOnly;
	}

	public function getPdo(): \PDO {
		return $this->pdo;
	}

	public function getTableName(): string {
		return $this->tableName;
	}

	public function setName(string $name): self {
		$this->name = $name;

		return $this;
	}

	public function setLifeTime(int $lifeTime): self {
		$this->lifeTime = $lifeTime;

		return $this;
	}

	public function setCookiePath(string $cookiePath): self {
		$this->cookiePath = $cookiePath;

		return $this;
	}

	public function setCookieDomain(string $cookieDomain): self {
		$this->cookieDomain = $cookieDomain;

		return $this;
	}

	public function setCookieSecure(bool $cookieSecure): self {
		$this->cookieSecure = $cookieSecure;

		return $this;
	}

	public function setCookieHttpOnly(bool $cookieHttpOnly): self {
		$this->cookieHttpOnly = $cookieHttpOnly;

		return $this;
	}

	public function setPdo(\PDO $pdo): self {
		$this->pdo = $pdo;

		return $this;
	}

	public function setTableName(string $tableName): self {
		$this->tableName = $tableName;

		return $this;
	}

	private function startNew() {
		$this->id = md5(uniqid('heminei', TRUE));
		$q = new \HemiFrame\Lib\SQLBuilder\Query([
			"pdo" => $this->getPdo()
		]);
		$q->insertInto($this->getTableName())->set("sessionId", $this->getId())->set("name", $this->getName());
		$q->set("expiryDate", date("Y-m-d H:i:s", time() + $this->getLifeTime()));
		$q->execute();
		setcookie($this->getName(), $this->getId(), time() + $this->getLifeTime()
			, $this->getCookiePath(), $this->getCookieDomain()
			, $this->getCookieSecure(), $this->getCookieHttpOnly());
	}

	/**
	 *
	 * @return boolean
	 */
	private function validate(): bool {
		if ($this->getId() != NULL) {
			$q = new \HemiFrame\Lib\SQLBuilder\Query([
				"pdo" => $this->getPdo()
			]);
			$q->select()->from($this->getTableName());
			$q->where("sessionId", $this->getId())->andWhere("name", $this->getName())->andWhere("expiryDate >= :expiryDate");
			$q->setVar("expiryDate", date("Y-m-d H:i:s"));
			$q->execute();

			$rows = $q->fetchObjects();
			if (is_array($rows) && count($rows) == 1) {
				$this->data = unserialize($rows[0]->data);
				return true;
			}
		}
		return false;
	}

}
