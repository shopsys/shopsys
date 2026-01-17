<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Security\AccessControl;

use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

interface RouteAccessCheckerInterface
{
    /**
     * Check if current user has access to a specific route with given HTTP method
     */
    public function hasAccess(string $routeName, HttpMethod|string $httpMethod): bool;
}
