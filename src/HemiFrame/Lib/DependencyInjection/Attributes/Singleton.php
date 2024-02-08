<?php

namespace HemiFrame\Lib\DependencyInjection\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Singleton
{
    public function __construct(
    ) {
    }
}
