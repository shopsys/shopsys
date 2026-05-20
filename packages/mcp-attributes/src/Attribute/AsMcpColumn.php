<?php

declare(strict_types=1);

namespace Shopsys\McpAttributes\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class AsMcpColumn
{
    public function __construct(
        public readonly bool $exposed = true,
    ) {
    }
}
