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
}
