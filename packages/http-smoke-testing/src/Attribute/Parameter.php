<?php

declare(strict_types=1);

namespace Shopsys\HttpSmokeTesting\Attribute;

class Parameter
{
    /**
     * @param string $name
     * @param string $value
     */
    public function __construct(
        public string $name,
        public string $value,
    ) {
    }
}
