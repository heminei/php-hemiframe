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
     * @var int
     */
    private $totalCount;

    public function __construct(int $index = 1)
    {
        $this->index = $index;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getIndex();
    }

    /**
     * @return int Current index
     */
    public function getIndex(): int
    {
        return $this->index;
    }

    public function setIndex(int $index)
    {
        $this->index = $index;
    }

    public function isOdd(): bool
    {
        if (0 != $this->index % 2) {
            return true;
        }

        return false;
    }

    public function isEven(): bool
    {
        if (0 == $this->index % 2) {
            return true;
        }

        return false;
    }

    public function isLast(): bool
    {
        if ($this->index == $this->totalCount) {
            return true;
        }

        return false;
    }

    public function isFirst(): bool
    {
        if (1 == $this->index) {
            return true;
        }

        return false;
    }

    public function getTotalCount(): int
    {
        return $this->totalCount;
    }

    public function setTotalCount(int $count)
    {
        $this->totalCount = $count;
    }
}
