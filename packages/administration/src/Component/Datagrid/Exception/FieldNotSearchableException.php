<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;
use Throwable;

/**
 * A field declared `searchable` cannot be searched — it has no path of its own, or its path leads to a value
 * the medium cannot search text in. Refused when the datagrid is built, so that the declaration fails in
 * development instead of the query failing for the administrator.
 */
class FieldNotSearchableException extends Exception
{
    public function __construct(string $fieldName, string $reason, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Field "%s" cannot be searchable: %s', $fieldName, $reason), 0, $previous);
    }
}
