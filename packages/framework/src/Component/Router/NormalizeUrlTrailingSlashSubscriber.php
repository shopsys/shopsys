<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class NormalizeUrlTrailingSlashSubscriber implements EventSubscriberInterface
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
     * @inheritDoc
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        if ($event->getThrowable() instanceof NotFoundHttpException) {
            $pathInfo = $event->getRequest()->getPathInfo();

            if (substr($pathInfo, -1) === '/') {
                $pathInfo = rtrim($pathInfo, '/');
            } else {
                $pathInfo .= '/';
            }

            $this->redirectIfPathExists($pathInfo, $event);
        }
    }

    /**
     * @param string $pathInfo
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    protected function redirectIfPathExists(string $pathInfo, ExceptionEvent $event): void
    {
        try {
            $this->router->match($pathInfo);

            $event->setResponse(new RedirectResponse($pathInfo, 301));
        } catch (ResourceNotFoundException $exception) {
        }
    }
}
