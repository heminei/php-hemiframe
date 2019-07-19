<?php

namespace HemiFrame\Lib\Loop;

/**
 * @author heminei <heminei@heminei.com>
 */
class Iteration
{

    /**
     * @var int
     */
    private $index;

    /**
     *
     * @var int
     */
    private $totalCount;

    public function __construct(int $index = 1)
    {
        $this->index = $index;
    }

    /**
     * @return int
     */
    public function __toString()
    {
        return $this->getIndex();
    }

    /**
     * @return int Current index
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    /**
     *
     * @param int $index
     */
    public function setIndex(int $index)
    {
        $this->index = $index;
    }

    /**
     *
     * @return boolean
     */
    public function isOdd(): bool
    {
        if (($this->index) % 2 != 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isEven(): bool
    {
        if (($this->index) % 2 == 0) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isLast(): bool
    {
        if ($this->index == $this->totalCount) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isFirst(): bool
    {
        if ($this->index == 1) {
            return true;
        }
        return false;
    }

    /**
     *
     * @return int
     */
    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    /**
     *
     * @param int $count
     */
    public function setTotalCount(int $count)
    {
        $this->totalCount = $count;
    }

}
