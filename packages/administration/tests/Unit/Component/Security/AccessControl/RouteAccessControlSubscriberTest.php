<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Security\AccessControl;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\AccessControlRuleFactory;
use Shopsys\AdministrationBundle\Component\Security\AccessControl\RouteAccessControlSubscriber;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Attribute\RequireRole;
use Shopsys\FrameworkBundle\Component\Security\Role\Role;
use Shopsys\FrameworkBundle\Component\Security\Role\RoleRegistryInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\SystemRole;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

final class RouteAccessControlSubscriberTest extends TestCase
{
    public function testMainRequestWithoutAccessIsDenied(): void
    {
        $routeAccessCheckerMock = $this->createMock(RouteAccessCheckerInterface::class);
        $routeAccessCheckerMock
            ->expects($this->once())
            ->method('hasAccess')
            ->with('admin_uncovered', 'GET')
            ->willReturn(false);
        $subscriber = $this->createRouteAccessControlSubscriber($routeAccessCheckerMock);
        $request = new Request(attributes: ['_route' => 'admin_uncovered']);
        $event = $this->createControllerEvent(fn () => null, $request, HttpKernelInterface::MAIN_REQUEST);

        $this->expectException(AccessDeniedException::class);
        $subscriber->onKernelController($event);
    }

    public function testSubrequestWithoutAccessControlRulesIsNotDenied(): void
    {
        $routeAccessCheckerMock = $this->createMock(RouteAccessCheckerInterface::class);
        $routeAccessCheckerMock
            ->expects($this->never())
            ->method('hasAccess');
        $securityMock = $this->createMock(Security::class);
        $securityMock
            ->expects($this->never())
            ->method('isGranted');
        $subscriber = $this->createRouteAccessControlSubscriber($routeAccessCheckerMock, $securityMock);
        $event = $this->createControllerEvent(
            [new RouteAccessControlSubscriberNoRulesController(), 'action'],
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $subscriber->onKernelController($event);
    }

    public function testSubrequestWithAccessControlRulesIsDeniedWhenRoleIsMissing(): void
    {
        $securityMock = $this->createMock(Security::class);
        $securityMock
            ->expects($this->once())
            ->method('isGranted')
            ->with(SystemRole::ADMIN)
            ->willReturn(false);
        $subscriber = $this->createRouteAccessControlSubscriber(security: $securityMock);
        $event = $this->createControllerEvent(
            [new RouteAccessControlSubscriberProtectedController(), 'action'],
            new Request(),
            HttpKernelInterface::SUB_REQUEST,
        );

        $this->expectException(AccessDeniedException::class);
        $subscriber->onKernelController($event);
    }

    private function createRouteAccessControlSubscriber(
        ?RouteAccessCheckerInterface $routeAccessChecker = null,
        ?Security $security = null,
    ): RouteAccessControlSubscriber {
        return new RouteAccessControlSubscriber(
            $routeAccessChecker ?? $this->createStub(RouteAccessCheckerInterface::class),
            $this->createAdminContextResolver(),
            new AttributeProcessor(new AccessControlRuleFactory($this->createRoleRegistry())),
            $security ?? $this->createStub(Security::class),
        );
    }

    private function createAdminContextResolver(): ContextResolverInterface
    {
        $contextResolverStub = $this->createStub(ContextResolverInterface::class);
        $contextResolverStub
            ->method('isCurrentContext')
            ->willReturn(true);

        return $contextResolverStub;
    }

    private function createRoleRegistry(): RoleRegistryInterface
    {
        $roleRegistryStub = $this->createStub(RoleRegistryInterface::class);
        $roleRegistryStub
            ->method('getRole')
            ->willReturnCallback(fn (string $roleIdentifier): Role => new Role($roleIdentifier, $roleIdentifier));

        return $roleRegistryStub;
    }

    private function createControllerEvent(
        callable $controller,
        Request $request,
        int $requestType,
    ): ControllerEvent {
        return new ControllerEvent(
            $this->createStub(HttpKernelInterface::class),
            $controller,
            $request,
            $requestType,
        );
    }
}

final class RouteAccessControlSubscriberNoRulesController
{
    public function action(): void
    {
    }
}

final class RouteAccessControlSubscriberProtectedController
{
    #[RequireRole(SystemRole::ADMIN)]
    public function action(): void
    {
    }
}
