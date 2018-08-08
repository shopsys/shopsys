<?php

namespace Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\Exception;

use Exception;

class FriendlyUrlRouteNotFoundException extends Exception implements FriendlyUrlException
{
    public function __construct(string $routeName, string $routerResourceFilepath)
    {
        parent::__construct(
            sprintf('Friendly URL route "%s" not found in "%s".', $routeName, realpath($routerResourceFilepath))
        );
    }
}
