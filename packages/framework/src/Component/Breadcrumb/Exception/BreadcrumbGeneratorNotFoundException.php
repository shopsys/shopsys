<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Breadcrumb\Exception;

use Exception;

class BreadcrumbGeneratorNotFoundException extends Exception
{
    /**
     * @param string $routeName
     */
    public function __construct($routeName, ?Exception $previous = null)
    {
        parent::__construct('Breadcrumb generator not found for route "' . $routeName . '"', 0, $previous);
    }
}
