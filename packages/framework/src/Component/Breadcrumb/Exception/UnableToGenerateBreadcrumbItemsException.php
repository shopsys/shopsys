<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb\Exception;

use Exception;

class UnableToGenerateBreadcrumbItemsException extends Exception implements BreadcrumbException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(?Exception $previous = null)
    {
        parent::__construct('', 0, $previous);
    }
}
