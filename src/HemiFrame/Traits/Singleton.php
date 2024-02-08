<?php

namespace HemiFrame\Traits;

/**
 * @author heminei <heminei@heminei.com>
 */
trait Singleton
{
    private static $_instance;

    public static function instance(): self
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }
}
