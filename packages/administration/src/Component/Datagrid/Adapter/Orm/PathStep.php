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
     */
    public function __construct(
        public PathStepKind $kind,
        public string $field,
        public string $partPath,
        public ?string $fieldType,
    ) {
    }
}
