<?php

namespace HemiFrame\Interfaces\Config;

/**
 * @author heminei
 */
interface Cache
{
    public function getGlobalPrefix();

    public function getDefaultCacheTime();

    public function isUseCache();
}
