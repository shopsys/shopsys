<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class NormalizeUrlTrailingSlashListener
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter
     */
    protected $router;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $router
     */
    public function __construct(CurrentDomainRouter $router)
    {
        $this->router = $router;
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $pathInfo = $event->getRequest()->getPathInfo();

            if (substr($pathInfo, -1) === '/') {
                try {
                    $pathInfo = rtrim($pathInfo, ' /');

                    $routerData = $this->router->match($pathInfo);

                    $this->setRedirectResponse($routerData, $event);
                } catch (ResourceNotFoundException $exception) {
                }
            } elseif (substr($pathInfo, -1) !== '/') {
                try {
                    $pathInfo .= '/';

                    $routerData = $this->router->match($pathInfo);

                    $this->setRedirectResponse($routerData, $event);
                } catch (ResourceNotFoundException $exception) {
                }
            }
        }
    }

    /**
     * @param array $routerData
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    protected function setRedirectResponse(array $routerData, ExceptionEvent $event): void
    {
        // Filter parameters of redirect controller
        if (array_key_exists('_controller', $routerData)
            && $routerData['_controller'] === 'FrameworkBundle:Redirect:redirect'
        ) {
            unset($routerData['route'], $routerData['permanent']);
        }

        // Filter route parameters
        $parameters = array_replace($event->getRequest()->query->all(), array_filter($routerData, function ($key) {
            return substr($key, 0, 1) !== '_';
        }, ARRAY_FILTER_USE_KEY));

        $url = $this->router->generate($routerData['_route'], $parameters);

        $event->setResponse(new RedirectResponse($url, 301));
    }
}
