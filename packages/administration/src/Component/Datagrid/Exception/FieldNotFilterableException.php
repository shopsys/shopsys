<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;
use Throwable;

/**
 * A field declared `filterable` cannot get a filter picked for it — it has no path of its own, the adapter
 * cannot describe the path, or no filter is known for the kind of value the path leads to. Refused when the
 * datagrid is built; declare the filter explicitly by `Datagrid::filters()` under the name of the field instead.
 */
class FieldNotFilterableException extends Exception
{
    public function __construct(string $fieldName, string $reason, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Field "%s" cannot be filterable: %s', $fieldName, $reason), 0, $previous);
    }
}
