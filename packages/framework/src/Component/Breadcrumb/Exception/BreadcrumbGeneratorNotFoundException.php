<?php

namespace Shopsys\FrameworkBundle\Component\Breadcrumb\Exception;

use Exception;

class BreadcrumbGeneratorNotFoundException extends Exception implements BreadcrumbException
{
    /**
     * @param \Exception|null $previous
     */
    public function __construct(string $routeName, Exception $previous = null)
    {
        parent::__construct('Breadcrumb generator not found for route "' . $routeName . '"', 0, $previous);
    }
}
