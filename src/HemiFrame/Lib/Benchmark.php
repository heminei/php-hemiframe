<?php

namespace HemiFrame\Lib;

/**
 * @author heminei <heminei@heminei.com>
 */
class Benchmark
{
    private $startTime;
    private $stopTime;
    private $startMemoryUsed;
    private $stopMemoryUsed;
    private $startIncludedFiles;
    private $endIncludedFiles;

    public function __construct()
    {
    }

    /**
     * Start time checker.
     */
    public function start(): self
    {
        $this->startTime = microtime(true);
        $this->startMemoryUsed = memory_get_usage();
        $this->startIncludedFiles = count(get_included_files());

        return $this;
    }

    /**
     * Stop time checker.
     */
    public function stop(): self
    {
        $this->stopTime = microtime(true);
        $this->stopMemoryUsed = memory_get_usage();
        $this->endIncludedFiles = count(get_included_files());

        return $this;
    }

    /**
     * Get execute time.
     */
    public function getExecuteTime(): float
    {
        return $this->stopTime - $this->startTime;
    }

    /**
     * Get memory used.
     */
    public function getMemoryUsed(): int
    {
        return $this->stopMemoryUsed - $this->startMemoryUsed;
    }

    /**
     * Get included files count.
     */
    public function getIclidedFilesCount(): int
    {
        return $this->startIncludedFiles - $this->endIncludedFiles;
    }

    /**
     * Get server load.
     *
     * @param int $type - 0 (all loads array), 1 (last 1 min), 2 (last 5 min), 3 (last 15 min)
     */
    public function getServerLoad(int $type = 0)
    {
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
        } else {
            $load = [false, false, false];
        }
        $return = null;
        if (0 === $type) {
            $return = $load;
        } elseif (1 === $type) {
            $return = $load[0];
        } elseif (2 === $type) {
            $return = $load[1];
        } elseif (3 === $type) {
            $return = $load[2];
        }

        return $return;
    }
}
