<?php

declare(strict_types=1);

namespace App\Component\Maintenance;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class MaintenanceModeSubscriber implements EventSubscriberInterface
{
    public const MAINTENANCE_CACHE_KEY = 'maintenance';

    /**
     * @var \Doctrine\Common\Cache\CacheProvider
     */
    private $cache;

    /**
     * @var \Twig\Environment
     */
    private $twig;

    /**
     * @var bool|null
     */
    private $isMaintenanceMode = null;

    /**
     * @param \Doctrine\Common\Cache\CacheProvider $cacheInterface
     * @param \Twig\Environment $twigEnvironment
     */
    public function __construct(CacheProvider $cacheInterface, Environment $twigEnvironment)
    {
        $this->cache = $cacheInterface;
        $this->twig = $twigEnvironment;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['enableMaintenanceOnRequest', 1000000],
        ];
    }

    /**
     * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $responseEvent
     */
    public function enableMaintenanceOnRequest(GetResponseEvent $responseEvent): void
    {
        if ($this->isMaintenanceMode === null) {
            $this->isMaintenanceMode = $this->cache->contains(self::MAINTENANCE_CACHE_KEY);
        }

        if ($this->isMaintenanceMode === false
            || in_array(PHP_SAPI, ['cli', 'cli-server', 'phpdbg'], true)
        ) {
            return;
        }

        $responseEvent->setResponse(
            new Response(
                $this->twig->render('maintenance.html.twig'),
                Response::HTTP_SERVICE_UNAVAILABLE,
                [
                    'Retry-after' => 60,
                ]
            )
        );

        $responseEvent->stopPropagation();
    }
}
