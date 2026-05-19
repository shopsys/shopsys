<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Security\AccessControl;

use Override;
use ReflectionClass;
use Shopsys\AdministrationBundle\Component\Security\Attribute\AttributeProcessor;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\HttpFoundation\HttpMethod;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;

class LiveComponentAccessControlSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ContextResolverInterface $contextResolver,
        protected readonly AttributeProcessor $attributeProcessor,
        protected readonly Security $security,
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

    public function onKernelController(ControllerEvent $event): void
    {
        if (!$this->contextResolver->isCurrentContext(AdminContext::class)) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->attributes->has('_live_component')) {
            return;
        }

        if ($request->attributes->get('_component_default_action', false)) {
            return;
        }

        $controllerCallable = $event->getController();

        if (!is_array($controllerCallable) || count($controllerCallable) !== 2) {
            return;
        }

        [$component, $method] = $controllerCallable;

        if (!is_object($component)) {
            return;
        }

        $reflectionClass = new ReflectionClass($component);

        if ($reflectionClass->getAttributes(AsLiveComponent::class) === []) {
            return;
        }

        $accessControlRules = $this->attributeProcessor->processMethod($reflectionClass, $reflectionClass->getMethod($method));

        if ($accessControlRules === []) {
            return;
        }

        $accessControlData = new RouteAccessControlData(
            null,
            $accessControlRules,
            $reflectionClass->getName(),
            $method,
        );

        $httpMethod = HttpMethod::getValidHttpMethod($request->getMethod());
        $hasAccess = $accessControlData->hasAccess($httpMethod, fn (string $role): bool => $this->security->isGranted($role));

        if (!$hasAccess) {
            throw new AccessDeniedException('Access denied for this Live Component action.');
        }
    }
}
