<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception;

use Exception;

class FriendlyUrlRouteNotFoundException extends Exception
{
    public function __construct(string $routeName, string $routerResourceFilepath)
    {
        parent::__construct(
            sprintf('Friendly URL route "%s" not found in "%s".', $routeName, realpath($routerResourceFilepath)),
        );
    }
}
