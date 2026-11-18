<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Path;

/**
 * What a dot notation path leads to, described without building any query.
 */
final readonly class PathDescription
{
    /**
     * The segment reaching the translated fields of a translatable entity — `translations.name`.
     */
    public const string TRANSLATIONS_SEGMENT = 'translations';

    /**
     * @param class-string|null $targetEntityClass The entity a path ending with an association leads to, null for a value
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Path\IntegerRange|null $integerRange The integers the adapter stores on the path, null for any other value or when the adapter sets no bound
     */
    public function __construct(
        public string $path,
        public PathCardinalityEnum $cardinality,
        public PathValueTypeEnum $valueType,
        public ?string $targetEntityClass = null,
        public ?IntegerRange $integerRange = null,
    ) {
    }

    public function isToMany(): bool
    {
        return $this->cardinality === PathCardinalityEnum::TO_MANY;
    }

    public function isToOne(): bool
    {
        return $this->cardinality === PathCardinalityEnum::TO_ONE;
    }

    public function withValueType(PathValueTypeEnum $valueType): self
    {
        return new self($this->path, $this->cardinality, $valueType, $this->targetEntityClass, $this->integerRange);
    }
}
