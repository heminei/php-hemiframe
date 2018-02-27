<?php

namespace HemiFrame\Interfaces\Config;

/**
 * Description of Interface
 *
 * @author heminei
 */
interface Cache {

	public function getGlobalPrefix();

	public function getDefaultCacheTime();

	public function isUseCache();
}
