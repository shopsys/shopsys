<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Exception;

use Exception;

class FilterNotConfiguredException extends Exception
{
    public static function noApplyCallback(string $filterName): self
    {
        return new self(sprintf(
            'Advanced search filter "%s" has no apply callback set. Set the query logic via apply().',
            $filterName,
        ));
    }
}
