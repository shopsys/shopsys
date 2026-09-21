<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Doctrine\Persistence\ManagerRegistry;
use Override;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\VarExporter\LazyObjectInterface;

class CloseIdleConnectionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly RedisFacade $redisFacade,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [WorkerRunningEvent::class => 'onWorkerRunning'];
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if (!$event->isWorkerIdle()) {
            return;
        }

        foreach ($this->managerRegistry->getConnections() as $connection) {
            $connection->close();
        }

        foreach ($this->redisFacade->getConnections() as $redis) {
            // Redis clients are lazy proxies, so closing one that was never used would first connect it (including a DNS lookup)
            if ($redis instanceof LazyObjectInterface && !$redis->isLazyObjectInitialized()) {
                continue;
            }

            $redis->close();
        }
    }
}
