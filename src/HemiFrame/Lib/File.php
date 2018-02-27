<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class File {

	private $name;
	private $size;
	private $type;
	private $md5;
	private $mimeType;
	private $path;
	private $destinationPath;

	public function __construct($file = NULL) {
		if ($file !== NULL) {
			$this->setFile($file);
		}
	}

	/**
	 *
	 * @param array|string $file
	 * @return \self
	 * @throws \Exception
	 */
	public function setFile($file): self {
		if (is_array($file)) {
			if (empty($file['tmp_name'])) {
				throw new \Exception("Invalid file array. Arays must be _FILES[file]");
			}
			$pathinfo = pathinfo($file['name']);
			$this->path = $file['tmp_name'];
			$this->name = $file['name'];
			$this->size = $file['size'];
			$this->mimeType = $file['type'];
		} else if (is_string($file)) {
			if (!is_readable($file)) {
				throw new \Exception("$file - The file can't be read");
			}
			$pathinfo = pathinfo($file);
			$this->path = $file;
			$this->name = $pathinfo['basename'];
			$this->size = filesize($file);
			$this->mimeType = mime_content_type($file);
		} else {
			throw new \Exception("Invalid file");
		}
		$this->md5 = md5_file($this->path);
		$this->type = strtolower($pathinfo['extension']);

		return $this;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getSize(): int {
		return $this->size;
	}

	public function getType(): string {
		return $this->type;
	}

	public function getMd5(): string {
		return $this->md5;
	}

	public function getMimeType(): string {
		return $this->mimeType;
	}

	public function getPath(): string {
		return $this->path;
	}

	public function getDestinationPath() {
		return $this->destinationPath;
	}

	/**
	 *
	 * @param string $path
	 * @param int $mode
	 * @return \self
	 */
	public function setDestinationPath(string $path, $mode = 0777): self {
		$this->destinationPath = $path;
		if (!is_dir($path)) {
			if (!mkdir($this->destinationPath, $mode, true)) {
				throw new \Exception("Can't generate destination path: $path");
			}
		}

		return $this;
	}

	/**
	 *
	 * @param string $path
	 * @param int $mode
	 * @return self
	 * @throws \Exception
	 */
	public function generateDestinationPath(string $path, $mode = 0777): self {
		$y = date('Y');
		$w = date('m');
		$j = date('j');
		$path = str_replace("//", "/", "$path/$y/$w/$j/");
		if (!file_exists($path) && !mkdir($path, $mode, true)) {
			throw new \Exception("Can't generate destination path: $path");
		}
		$this->destinationPath = $path . $this->md5 . "." . $this->type;

		return $this;
	}

	public function copy(): bool {
		if (empty($this->destinationPath)) {
			throw new \Exception("Destination Path is empty.");
		}
		return copy($this->path, $this->destinationPath);
	}

	public function moveUploadedFile(): bool {
		if (empty($this->destinationPath)) {
			throw new \Exception("Destination Path is empty.");
		}
		return move_uploaded_file($this->path, $this->destinationPath);
	}

}
