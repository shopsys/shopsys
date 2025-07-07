<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listener that enforces route-level access control with HTTP method restrictions
 */
final class RouteAccessControlSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface $routeAccessChecker
     * @param \Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface $contextResolver
     */
    public function __construct(
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
        private readonly ContextResolverInterface $contextResolver,
    ) {
    }

    /**
     * @return array<string, array<int|string, int|string>>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 0],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
    public function onKernelController(ControllerEvent $event): void
    {
        // Only apply to admin routes
        if ($event->isMainRequest() === false || !$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        $request = $event->getRequest();
        $routeName = $request->attributes->get('_route');

        if ($routeName === null) {
            return;
        }

        // Check route access with HTTP method restrictions
        if (!$this->routeAccessChecker->hasAccess($routeName, $request->getMethod())) {
            throw new AccessDeniedException('Access denied for this route and HTTP method combination.');
        }
    }
}
