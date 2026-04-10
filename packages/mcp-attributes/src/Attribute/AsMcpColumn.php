<?php

declare(strict_types=1);

namespace Shopsys\McpAttributes\Attribute;

use Attribute;

/**
 * Use on a property to expose or hide that mapped property. Use on a class with fieldName
 * to configure exposure for a mapped field or association without a property-level attribute,
 * for example when extending a vendor class that is missing MCP exposure configuration.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class AsMcpColumn
{
    public function __construct(
        public readonly bool $exposed = true,
        public readonly ?string $fieldName = null,
    ) {
    }
}
