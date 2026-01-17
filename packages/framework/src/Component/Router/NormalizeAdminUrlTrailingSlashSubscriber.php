<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Router;

use Override;
use Shopsys\FrameworkBundle\Component\Context\AdminContext;
use Shopsys\FrameworkBundle\Component\Context\ContextResolverInterface;
use Shopsys\FrameworkBundle\Component\String\TransformStringHelper;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

class NormalizeAdminUrlTrailingSlashSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly AdministrationRouter $administrationRouter,
        protected readonly TransformStringHelper $transformStringHelper,
        protected readonly ContextResolverInterface $contextResolver,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException'],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        if (!$this->contextResolver->isCurrentContext(AdminContext::class) || !$event->getThrowable() instanceof NotFoundHttpException) {
            return;
        }

        $pathInfo = $event->getRequest()->getPathInfo();
        $pathInfo = $this->transformStringHelper->addOrRemoveTrailingSlashFromString($pathInfo);

        // prevents invalid redirection if request URL is http://host/index.php as $pathInfo is empty in that case
        if ($pathInfo !== '') {
            $this->redirectToExistingPath($pathInfo, $event);
        }
    }

    protected function redirectToExistingPath(string $newPath, ExceptionEvent $event): void
    {
        try {
            $this->administrationRouter->match($newPath);

            $uri = $event->getRequest()->getUri();
            $httpHost = $event->getRequest()->getHttpHost();
            $pathInfo = $event->getRequest()->getPathInfo();

            $fullPathBefore = $httpHost . $pathInfo;
            $fullPathAfter = $httpHost . $newPath;
            $pathToRedirect = $this->transformStringHelper->replaceOccurrences($fullPathBefore, $fullPathAfter, $uri, 1);

            $event->setResponse(new RedirectResponse($pathToRedirect, 301));
        } catch (ResourceNotFoundException $exception) {
            return;
        }
    }
}
