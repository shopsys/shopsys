<?php

declare(strict_types=1);

namespace Shopsys\McpAttributes\Attribute;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY)]
class AsMcpTable
{
    public function __construct(
        public readonly bool $exposed = true,
    ) {
    }
}
