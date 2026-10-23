<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

/**
 * One segment of a walked path with everything a query needs to reproduce it.
 *
 * @internal this class is not intended to be used directly by other developers
 */
final readonly class PathStep
{
    /**
     * @param string $partPath The path up to and including this segment
     * @param string|null $fieldType Doctrine type of the value a terminal segment leads to
     * @param class-string|null $targetEntityClass The entity an association segment leads to
     * @param string|null $mappedBy The association of the target entity owning an association segment, null when the segment owns it
     * @param string[] $targetIdentifierFields The identifier fields of the entity an association segment owns the reference to, given only when it is identified by several fields
     * @param string|null $identifierField The identifier field an identity segment reads, given only when the entity it leads to is identified by several fields
     * @param bool $isToMany Whether an association segment leads to a collection
     */
    public function __construct(
        public PathStepKind $kind,
        public string $field,
        public string $partPath,
        public ?string $fieldType,
        public ?string $targetEntityClass = null,
        public ?string $mappedBy = null,
        public array $targetIdentifierFields = [],
        public ?string $identifierField = null,
        public bool $isToMany = false,
    ) {
    }
}
