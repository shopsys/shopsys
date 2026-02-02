<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router\Security;

use Override;
use ReflectionMethod;
use Shopsys\FrameworkBundle\Component\Cache\InMemoryCache;
use Shopsys\FrameworkBundle\Component\Reflection\ReflectionHelper;
use Shopsys\FrameworkBundle\Component\Router\Security\Attribute\CsrfProtection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RouteCsrfProtector implements EventSubscriberInterface
{
    public const string CSRF_TOKEN_REQUEST_PARAMETER = 'routeCsrfToken';
    public const string CSRF_TOKEN_ID_PREFIX = 'route_';
    protected const string CSRF_ROUTES_CACHE_NAMESPACE = 'csrfCheckedRoutes';

    public function __construct(
        protected readonly CsrfTokenManagerInterface $tokenManager,
        protected readonly InMemoryCache $inMemoryCache,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 80],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        if ($this->isProtected($event)) {
            $request = $event->getRequest();
            $csrfToken = $request->get(self::CSRF_TOKEN_REQUEST_PARAMETER);
            $routeName = $request->get('_route');

            if ($csrfToken === null || !$this->isCsrfTokenValid($routeName, $csrfToken)) {
                throw new BadRequestHttpException('Csrf token is invalid');
            }
        }
    }

    public function getCsrfTokenId(string $routeName): string
    {
        return static::CSRF_TOKEN_ID_PREFIX . $routeName;
    }

    public function getCsrfTokenByRoute(string $routeName): string
    {
        return $this->tokenManager->getToken($this->getCsrfTokenId($routeName))->getValue();
    }

    protected function isCsrfTokenValid(string $routeName, string $csrfToken): bool
    {
        $token = new CsrfToken($this->getCsrfTokenId($routeName), $csrfToken);

        return $this->tokenManager->isTokenValid($token);
    }

    protected function isProtected(ControllerEvent $event): bool
    {
        if (!$event->isMainRequest()) {
            return false;
        }

        $eventController = $event->getController();

        if (is_array($eventController)) {
            [$controller, $action] = $eventController;
        } else {
            $controller = $eventController;
            $action = '__invoke';
        }

        return $this->isActionProtected($controller, $action);
    }

    /**
     * @param object|class-string $controller
     */
    public function isActionProtected(object|string $controller, string $actionMethod): bool
    {
        $controllerName = is_object($controller) ? get_class($controller) : $controller;

        return $this->inMemoryCache->getOrSaveValue(
            static::CSRF_ROUTES_CACHE_NAMESPACE,
            function () use ($controllerName, $actionMethod) {
                $method = new ReflectionMethod($controllerName, $actionMethod);

                return ReflectionHelper::getMethodAttribute($method, CsrfProtection::class) !== null;
            },
            $controllerName,
            $actionMethod,
        );
    }
}
