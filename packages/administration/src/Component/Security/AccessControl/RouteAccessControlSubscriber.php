<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Override;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\RouteAccessCheckerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Listener that enforces route-level access control with HTTP method restrictions
 */
final class RouteAccessControlSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly RouteAccessCheckerInterface $routeAccessChecker,
        private readonly ContextResolverInterface $contextResolver,
        private readonly AttributeProcessor $attributeProcessor,
        private readonly Security $security,
    ) {
    }

    /**
     * @return array<string, array<int|string, int|string>>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 90],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        if ($event->isMainRequest() === false) {
            $this->handleSubrequestAccess($event);

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

    private function handleSubrequestAccess(ControllerEvent $event): void
    {
        $controllerCallable = $event->getController();

        if (is_array($controllerCallable)) {
            [$controllerObject, $method] = $controllerCallable;
        } elseif (is_object($controllerCallable) && method_exists($controllerCallable, '__invoke')) {
            $controllerObject = $controllerCallable;
            $method = '__invoke';
        } else {
            return;
        }

        $reflectionClass = new ReflectionClass($controllerObject);
        $routeData = $this->attributeProcessor->processMethod($reflectionClass, $reflectionClass->getMethod($method));

        if ($routeData === []) {
            return;
        }

        $routeAccessControlData = new RouteAccessControlData(
            null,
            $routeData,
            get_class($controllerObject),
            $method,
        );

        $method = HttpMethod::getValidHttpMethod($event->getRequest()->getMethod());
        $hasAccess = $routeAccessControlData->hasAccess($method, fn (string $role) => $this->security->isGranted($role));

        if (!$hasAccess) {
            throw new AccessDeniedException('Access denied for this route and HTTP method combination.');
        }
    }
}
