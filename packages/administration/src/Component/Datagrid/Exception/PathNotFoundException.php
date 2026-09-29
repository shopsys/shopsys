<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

/**
 * A path addressing something the mapping does not have is a mistake in the code, so it is refused right away.
 */
class PathNotFoundException extends Exception
{
    /**
     * @param class-string $entityClass
     */
    public function __construct(string $path, string $missingSegment, string $entityClass)
    {
        parent::__construct(sprintf(
            'Path "%s" cannot be resolved, entity "%s" has no field or association "%s".',
            $path,
            $entityClass,
            $missingSegment,
        ));
    }
}
