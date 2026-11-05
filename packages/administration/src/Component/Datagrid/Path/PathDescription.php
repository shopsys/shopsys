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
     * @param int|null $minimumValue The least integer the medium stores on the path, null for any other value or when the medium sets no bound
     * @param int|null $maximumValue The greatest integer the medium stores on the path, null for any other value or when the medium sets no bound
     */
    public function __construct(
        public string $path,
        public PathCardinalityEnum $cardinality,
        public PathValueTypeEnum $valueType,
        public ?string $targetEntityClass = null,
        public ?int $minimumValue = null,
        public ?int $maximumValue = null,
    ) {
    }

    public function isToMany(): bool
    {
        return $this->cardinality === PathCardinalityEnum::TO_MANY;
    }
}
