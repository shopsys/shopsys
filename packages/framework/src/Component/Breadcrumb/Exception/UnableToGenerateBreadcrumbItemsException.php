<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb\Exception;

use Exception;

class UnableToGenerateBreadcrumbItemsException extends Exception implements BreadcrumbException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $message = '', Exception $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}
