<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * @internal this class is not intended to be used directly by other developers
 */
final readonly class WalkedPath
{
    /**
     * @param list<\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathStep> $steps
     */
    public function __construct(
        public array $steps,
        public PathDescription $description,
    ) {
    }

    /**
     * The Doctrine type of the value the path leads to, null when the path ends with an association.
     */
    public function getFieldType(): ?string
    {
        return $this->steps[array_key_last($this->steps)]->fieldType;
    }
}
