<?php

namespace HemiFrame\Traits;

/**
 * @author heminei <heminei@heminei.com>
 */
trait Singleton
{
    private static $_instance = null;

    /**
     *
     * @return self
     */
    public static function instance(): self
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
}
