<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;
use Throwable;

/**
 * A declared filter cannot work over the adapter of its datagrid — its path does not exist, leads to a value
 * none of its operations applies to, or needs something the medium cannot express. Refused when the
 * datagrid is built, so that the declaration fails in development instead of the query failing for the
 * administrator.
 */
class FilterNotApplicableException extends Exception
{
    public function __construct(string $filterName, string $reason, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Filter "%s" cannot be applied: %s', $filterName, $reason), 0, $previous);
    }
}
