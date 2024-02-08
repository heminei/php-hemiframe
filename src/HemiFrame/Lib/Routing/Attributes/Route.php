<?php

namespace HemiFrame\Lib\Routing\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Route
{
    public function __construct(
        public readonly string $url,
        public readonly string $key,
        public readonly int $priority = 1,
        public readonly ?string $host = null,
        public readonly ?string $lang = null,
    ) {
    }
}
