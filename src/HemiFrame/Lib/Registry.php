<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Registry {

	private $_data = [];

	public function __set(string $name, $value) {
		$this->_data[$name] = $value;
	}

	public function __get($name) {
		return $this->_data[$name];
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @return self
	 */
	public function setData(string $name, $value): self {
		$this->_data[$name] = $value;

		return $this;
	}

	/**
	 *
	 * @param string $name
	 * @return string
	 */
	public function getData($name) {
		return $this->_data[$name];
	}

	/**
	 *
	 * @return array
	 */
	public function getAllData(): array {
		return $this->_data;
	}

}
