<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Path;

/**
 * What a dot notation path leads to, described without touching any query — the answer a declaration needs
 * before anything is built.
 */
final readonly class PathDescription
{
    /**
     * @param class-string|null $targetEntityClass The entity a path ending with an association leads to, null for a value
     */
    public function __construct(
        public string $path,
        public PathCardinalityEnum $cardinality,
        public PathValueTypeEnum $valueType,
        public ?string $targetEntityClass = null,
    ) {
    }

    public function isToMany(): bool
    {
        return $this->cardinality === PathCardinalityEnum::TO_MANY;
    }
}
