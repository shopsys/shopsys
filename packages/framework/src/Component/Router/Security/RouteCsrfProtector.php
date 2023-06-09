<?php

namespace Shopsys\FrameworkBundle\Component\Router\Security;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RouteCsrfProtector implements EventSubscriberInterface
{
    public const CSRF_TOKEN_REQUEST_PARAMETER = 'routeCsrfToken';
    public const CSRF_TOKEN_ID_PREFIX = 'route_';

    protected Reader $annotationReader;

    protected CsrfTokenManagerInterface $tokenManager;

    /**
     * @param \Doctrine\Common\Annotations\Reader $annotationReader
     * @param \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface $tokenManager
     */
    public function __construct(Reader $annotationReader, CsrfTokenManagerInterface $tokenManager)
    {
        $this->annotationReader = $annotationReader;
        $this->tokenManager = $tokenManager;
    }

    /**
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     */
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

    /**
     * @param string $routeName
     * @return string
     */
    public function getCsrfTokenId(string $routeName): string
    {
        return static::CSRF_TOKEN_ID_PREFIX . $routeName;
    }

    /**
     * @param string $routeName
     * @return string
     */
    public function getCsrfTokenByRoute(string $routeName): string
    {
        return $this->tokenManager->getToken($this->getCsrfTokenId($routeName))->getValue();
    }

    /**
     * @param string $routeName
     * @param string $csrfToken
     * @return bool
     */
    protected function isCsrfTokenValid(string $routeName, string $csrfToken): bool
    {
        $token = new CsrfToken($this->getCsrfTokenId($routeName), $csrfToken);

        return $this->tokenManager->isTokenValid($token);
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ControllerEvent $event
     * @return bool
     */
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

        $method = new ReflectionMethod($controller, $action);
        $annotation = $this->annotationReader->getMethodAnnotation($method, CsrfProtection::class);

        return $annotation !== null;
    }
}
