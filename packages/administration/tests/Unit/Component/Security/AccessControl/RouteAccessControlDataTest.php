<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\AccessControl;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlData;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;

class RouteAccessControlDataTest extends TestCase
{
    public function testRouteWithoutAccessControlRulesDoesNotAllowAccess(): void
    {
        $routeAccessControlData = new RouteAccessControlData('admin_uncovered', [], 'TestController', 'testAction');

        $hasAccess = $routeAccessControlData->hasAccess(HttpMethod::GET, fn (): bool => true);

        $this->assertFalse($hasAccess);
    }
}
