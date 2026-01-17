<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Attribute;

class Parameter
{
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
