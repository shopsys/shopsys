<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * A datagrid field path resolved against a query — the joins it needs are in place and the value is
 * addressable by a DQL expression.
 */
final readonly class PathResolution
{
    /**
     * @param string|null $dqlExpression Expression usable in DQL `SELECT`, `WHERE` and `ORDER BY`. Null for a to-many path, which is never joined into the main query.
     * @param string $selectAlias Alias of the selected value. DQL allows it in `ORDER BY` but never in `WHERE`.
     * @param string|null $fieldType Doctrine type of the terminal field, null when the path ends with an association
     */
    public function __construct(
        public ?string $dqlExpression,
        public string $selectAlias,
        public ?string $fieldType,
        public PathDescription $description,
    ) {
    }

    public function isToMany(): bool
    {
        return $this->description->isToMany();
    }
}
