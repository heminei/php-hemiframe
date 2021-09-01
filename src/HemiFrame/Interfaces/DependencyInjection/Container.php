<?php

namespace HemiFrame\Interfaces\DependencyInjection;

/**
 * @author heminei
 */
interface Container
{
    public function get(string $key, array $arguments = []);
}
