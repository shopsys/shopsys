<?php

namespace Shopsys\FrameworkBundle\Component\Router\Security;

use Doctrine\Common\Annotations\Reader;
use ReflectionMethod;
use Shopsys\FrameworkBundle\Component\Router\Security\Annotation\CsrfProtection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class RouteCsrfProtector implements EventSubscriberInterface
{
    const CSRF_TOKEN_REQUEST_PARAMETER = 'routeCsrfToken';
    const CSRF_TOKEN_ID_PREFIX = 'route_';

    /**
     * @var \Doctrine\Common\Annotations\Reader
     */
    private $annotationReader;

    /**
     * @var \Symfony\Component\Security\Csrf\CsrfTokenManagerInterface
     */
    private $tokenManager;

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

    public function onKernelController(FilterControllerEvent $event)
    {
        if ($this->isProtected($event)) {
            $request = $event->getRequest();
            $csrfToken = $request->get(self::CSRF_TOKEN_REQUEST_PARAMETER);
            $routeName = $request->get('_route');

            if ($csrfToken === null || !$this->isCsrfTokenValid($routeName, $csrfToken)) {
                throw new \Symfony\Component\HttpKernel\Exception\BadRequestHttpException('Csrf token is invalid');
            }
        }
    }

    /**
     * @param string $routeName
     */
    public function getCsrfTokenId($routeName): string
    {
        return self::CSRF_TOKEN_ID_PREFIX . $routeName;
    }

    /**
     * @param string $routeName
     */
    public function getCsrfTokenByRoute($routeName): string
    {
        return $this->tokenManager->getToken($this->getCsrfTokenId($routeName))->getValue();
    }

    /**
     * @param string $routeName
     * @param string $csrfToken
     */
    private function isCsrfTokenValid($routeName, $csrfToken): bool
    {
        $token = new CsrfToken($this->getCsrfTokenId($routeName), $csrfToken);

        return $this->tokenManager->isTokenValid($token);
    }

    private function isProtected(FilterControllerEvent $event): bool
    {
        if (!$event->isMasterRequest()) {
            return false;
        }

        list($controller, $action) = $event->getController();
        $method = new ReflectionMethod($controller, $action);
        $annotation = $this->annotationReader->getMethodAnnotation($method, CsrfProtection::class);

        return $annotation !== null;
    }
}
