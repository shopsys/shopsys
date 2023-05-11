<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Shopsys\FrameworkBundle\Component\String\TransformString;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class NormalizeUrlTrailingSlashSubscriber implements EventSubscriberInterface
{
    protected CurrentDomainRouter $currentDomainRouter;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Router\CurrentDomainRouter $currentDomainRouter
     */
    public function __construct(CurrentDomainRouter $currentDomainRouter)
    {
        $this->currentDomainRouter = $currentDomainRouter;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
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
            $pathInfo = TransformString::addOrRemoveTrailingSlashFromString($pathInfo);

            // prevents invalid redirection if request URL is http://host/index.php as $pathInfo is empty in that case
            if ($pathInfo !== '') {
                $this->redirectToExistingPath($pathInfo, $event);
            }
        }
    }

    /**
     * @param string $newPath
     * @param \Symfony\Component\HttpKernel\Event\ExceptionEvent $event
     */
    protected function redirectToExistingPath(string $newPath, ExceptionEvent $event): void
    {
        try {
            $this->currentDomainRouter->match($newPath);

            $uri = $event->getRequest()->getUri();
            $httpHost = $event->getRequest()->getHttpHost();
            $pathInfo = $event->getRequest()->getPathInfo();

            $fullPathBefore = $httpHost . $pathInfo;
            $fullPathAfter = $httpHost . $newPath;
            $pathToRedirect = TransformString::replaceOccurences($fullPathBefore, $fullPathAfter, $uri, 1);

            $event->setResponse(new RedirectResponse($pathToRedirect, 301));
        } catch (ResourceNotFoundException $exception) {
            return;
        }
    }
}
