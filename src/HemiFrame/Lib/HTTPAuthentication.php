<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class HTTPAuthentication {

	/**
	 *
	 * @var array
	 */
	private $users = array();

	/**
	 *
	 * @var string
	 */
	private $message = NULL;

	public function __construct(string $message = NULL) {
		if ($message !== NULL) {
			$this->setMessage($message);
		} else {
			$this->setMessage("Access restricted");
		}
	}

	/**
	 *
	 * @return boolean
	 */
	public function login() {
		if (isset($_SERVER['PHP_AUTH_USER']) AND ( array_key_exists($_SERVER['PHP_AUTH_USER'], $this->getUsers())
			AND $_SERVER['PHP_AUTH_PW'] == $this->users[$_SERVER['PHP_AUTH_USER']])) {
			return true;
		} else {
			header('WWW-Authenticate: Basic realm="' . $this->getMessage() . '"');
			header('HTTP/1.0 401 Unauthorized');
			return false;
		}
	}

	/**
	 *
	 * @param string $user
	 * @param string $password
	 * @return \HemiFrame\Lib\HTTPAuthentication
	 */
	public function addUser(string $user, string $password): self {
		$this->users[$user] = $password;

		return $this;
	}

	/**
	 *
	 * @return array
	 */
	public function getUsers(): array {
		return $this->users;
	}

	/**
	 *
	 * @return string
	 */
	public function getMessage(): string {
		return $this->message;
	}

	/**
	 *
	 * @param string $message
	 * @return \self
	 */
	public function setMessage(string $message): self {
		$this->message = $message;

		return $this;
	}

}
