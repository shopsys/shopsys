<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception;

use Exception;

class FriendlyUrlRouteNotSupportedException extends Exception
{
    public function __construct(string $routeName)
    {
        parent::__construct('Generating friendly URL for route "' . $routeName . '" is not yet supported.');
    }
}
