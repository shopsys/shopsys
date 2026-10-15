<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Exception;

use Exception;

class FilterNotFoundException extends Exception
{
    public function __construct(string $filterName)
    {
        parent::__construct(sprintf('Filter "%s" is not declared on the datagrid.', $filterName));
    }
}
