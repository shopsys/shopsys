<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

/**
 * A path addressing nothing is a mistake in the code, not a record without a value, so it is refused the
 * same way a query refuses a field the entity does not have.
 */
class PathNotFoundInRowException extends Exception
{
    /**
     * @param string[] $availableKeys
     */
    public function __construct(string $path, string $missingSegment, array $availableKeys)
    {
        parent::__construct(sprintf(
            'Path "%s" cannot be read, there is no "%s" in the row. Available: "%s".',
            $path,
            $missingSegment,
            implode('", "', $availableKeys),
        ));
    }
}
