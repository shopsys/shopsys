<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Doctrine\Persistence\ManagerRegistry;
use Override;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

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
            $redis->close();
        }
    }
}
