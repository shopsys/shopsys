<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger;

use Doctrine\ORM\Exception\EntityManagerClosed;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Exception\StopWorkerException;

class ResetWorkerOnClosedEntityManagerSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Symfony\Component\Messenger\Event\WorkerMessageFailedEvent $event
     */
    public function onWorkerMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!($exception instanceof HandlerFailedException)) {
            return;
        }

        foreach ($exception->getNestedExceptions() as $nestedException) {
            if ($nestedException instanceof EntityManagerClosed) {
                throw new StopWorkerException();
            }
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => ['onWorkerMessageFailed', 10],
        ];
    }
}
