<?php

declare(strict_types=1);

namespace Shopsys\FrontendApiBundle\Component\Messenger;

use Overblog\GraphQLBundle\Event\Events;
use Overblog\GraphQLBundle\Event\ExecutorResultEvent;
use Override;
use Shopsys\FrameworkBundle\Component\HttpFoundation\SilencedExceptionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class GraphqlErrorResetDelayedEnvelopesSubscriber implements EventSubscriberInterface
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
    ) {
    }

    /**
     * @return array<string, string|array<int, array{0: string, 1?: int}>>
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            Events::POST_EXECUTOR => [
                ['onPostExecutor'],
            ],
        ];
    }

    public function onPostExecutor(ExecutorResultEvent $event): void
    {
        if (count($event->getResult()->errors) === 0) {
            return;
        }

        $this->eventDispatcher->dispatch(new SilencedExceptionEvent());
    }
}
