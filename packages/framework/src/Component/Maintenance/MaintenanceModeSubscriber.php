<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Maintenance;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly MaintenanceModeFacade $maintenanceModeFacade,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    public function enableMaintenanceOnRequest(RequestEvent $requestEvent): void
    {
        if ($this->maintenanceModeFacade->isEnabled() === false
            || in_array(PHP_SAPI, ['cli', 'cli-server', 'phpdbg'], true)
        ) {
            return;
        }

        $requestEvent->setResponse(
            new Response(
                $this->twigEnvironment->render('@ShopsysFramework/Common/maintenance.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE,
                [
                    'Retry-after' => 300,
                    'Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT',
                    'Cache-Control' => 'max-age=0, no-cache, must-revalidate, proxy-revalidate',
                    'Expires' => 'Thu, 01 Dec 1994 16:00:00 GMT',
                ],
            ),
        );

        $requestEvent->stopPropagation();
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['enableMaintenanceOnRequest', 10000],
        ];
    }
}
