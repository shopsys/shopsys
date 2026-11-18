<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * One segment of a walked path with everything a query needs to reproduce it.
 *
 * @internal model of the ORM adapter, may change without upgrade notes — implement `PathWalkerInterface`, typically by decorating `PathWalker`, and change only the `PathDescription` of the walked path instead
 */
final readonly class PathStep
{
    /**
     * @param string $partPath The path up to and including this segment
     * @param string|null $fieldType Doctrine type of the value a terminal segment leads to
     * @param class-string|null $targetEntityClass The entity an association segment leads to
     * @param string|null $mappedBy The association of the target entity owning an association segment, null when the segment owns it
     */
    public function __construct(
        public PathStepKind $kind,
        public string $field,
        public string $partPath,
        public ?string $fieldType,
        public ?string $targetEntityClass = null,
        public ?string $mappedBy = null,
        public bool $isToMany = false,
    ) {
    }
}
