<?php

declare(strict_types=1);

namespace Shopsys\McpAttributes\Attribute;

use Attribute;

/**
 * Use on a class to configure exposure for a mapped inherited field or association
 * without a property-level attribute, for example when extending a vendor class
 * that is missing MCP exposure configuration.
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::IS_REPEATABLE)]
class AsMcpInheritedColumn
{
    public function __construct(
        public readonly string $fieldName,
        public readonly bool $exposed = true,
    ) {
    }
}
