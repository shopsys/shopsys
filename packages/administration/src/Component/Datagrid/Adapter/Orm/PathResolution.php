<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * A dot notation path resolved against a query: the joins it needs are in place and its value has a DQL expression.
 */
final readonly class PathResolution
{
    /**
     * @param string|null $dqlExpression Expression usable in DQL `SELECT`, `WHERE` and `ORDER BY` — `currency_join.code`. Null for a to-many path in the main query, which never joins it.
     * @param string $selectAlias Alias of the selected value — `currency__code`. DQL allows it in `ORDER BY`, but not in `WHERE`.
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
