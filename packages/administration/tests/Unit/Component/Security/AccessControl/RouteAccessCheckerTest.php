<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\AccessControl;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Configuration\AccessControlConfiguration;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlDataProviderInterface;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessChecker;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Symfony\Bundle\SecurityBundle\Security;

class RouteAccessCheckerTest extends TestCase
{
    public function testExcludedRouteAllowsAccessWithoutRouteDataLookup(): void
    {
        $accessControlDataProviderMock = $this->createMock(AccessControlDataProviderInterface::class);
        $accessControlDataProviderMock
            ->expects($this->never())
            ->method('findRouteByName');

        $securityMock = $this->createMock(Security::class);
        $securityMock
            ->expects($this->never())
            ->method('isGranted');

        $routeAccessChecker = new RouteAccessChecker(
            $accessControlDataProviderMock,
            new AccessControlConfiguration(),
            $securityMock,
        );

        $hasAccess = $routeAccessChecker->hasAccess('admin_login', HttpMethod::GET);

        $this->assertTrue($hasAccess);
    }
}
