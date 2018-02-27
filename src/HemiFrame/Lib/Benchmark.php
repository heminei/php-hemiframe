<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Benchmark {

	private $startTime;
	private $stopTime;
	private $startMemoryUsed;
	private $stopMemoryUsed;
	private $startIncludedFiles;
	private $endIncludedFiles;

	public function __construct() {

	}

	/**
	 * Start time checker
	 * @return self
	 */
	public function start(): self {
		$this->startTime = microtime(true);
		$this->startMemoryUsed = memory_get_usage();
		$this->startIncludedFiles = count(get_included_files());
		return $this;
	}

	/**
	 * Stop time checker
	 * @return self
	 */
	public function stop(): self {
		$this->stopTime = microtime(true);
		$this->stopMemoryUsed = memory_get_usage();
		$this->endIncludedFiles = count(get_included_files());
		return $this;
	}

	/**
	 * Get execute time
	 * @return float
	 */
	public function getExecuteTime(): float {
		return $this->stopTime - $this->startTime;
	}

	/**
	 * Get memory used
	 * @return int
	 */
	public function getMemoryUsed(): int {
		return $this->stopMemoryUsed - $this->startMemoryUsed;
	}

	/**
	 * Get included files count
	 * @return int
	 */
	public function getIclidedFilesCount(): int {
		return $this->startIncludedFiles - $this->endIncludedFiles;
	}

	/**
	 * Get server load
	 * @param int $type - 0 (all loads array), 1 (last 1 min), 2 (last 5 min), 3 (last 15 min)
	 * @return mixed
	 */
	public function getServerLoad(int $type = 0) {
		if (function_exists("sys_getloadavg")) {
			$load = sys_getloadavg();
		} else {
			$load = array(FALSE, FALSE, FALSE);
		}
		if ($type === 0) {
			$return = $load;
		} elseif ($type === 1) {
			$return = $load[0];
		} elseif ($type === 2) {
			$return = $load[1];
		} elseif ($type === 3) {
			$return = $load[2];
		}
		return $return;
	}

}
