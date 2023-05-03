<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Maintenance;

use Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public const MAINTENANCE_KEY = 'maintenance';

    /**
     * @var bool|null
     */
    protected ?bool $isMaintenanceMode = null;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisClientFacade $redisClientFacade
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(
        protected readonly RedisClientFacade $redisClientFacade,
        protected readonly Environment $twigEnvironment,
    ) {
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\RequestEvent $requestEvent
     */
    public function enableMaintenanceOnRequest(RequestEvent $requestEvent): void
    {
        if ($this->isMaintenanceMode === null) {
            $this->isMaintenanceMode = $this->redisClientFacade->contains(self::MAINTENANCE_KEY);
        }

        if ($this->isMaintenanceMode === false
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
                ]
            ),
        );

        $requestEvent->stopPropagation();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['enableMaintenanceOnRequest', 1000000],
        ];
    }
}
