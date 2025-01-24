<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Doctrine\Persistence\ManagerRegistry;
use Shopsys\FrameworkBundle\Component\Redis\RedisFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;

class CloseIdleConnectionSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Doctrine\Persistence\ManagerRegistry $managerRegistry
     * @param \Shopsys\FrameworkBundle\Component\Redis\RedisFacade $redisFacade
     */
    public function __construct(
        protected readonly ManagerRegistry $managerRegistry,
        protected readonly RedisFacade $redisFacade,
    ) {
    }

    /**
     * @return iterable
     */
    public static function getSubscribedEvents(): iterable
    {
        yield WorkerRunningEvent::class => 'onWorkerRunning';
    }

    /**
     * @param \Symfony\Component\Messenger\Event\WorkerRunningEvent $event
     */
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
