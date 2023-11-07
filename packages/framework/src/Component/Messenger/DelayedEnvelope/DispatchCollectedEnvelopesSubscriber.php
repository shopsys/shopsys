<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Psr\Log\LoggerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Event\WorkerMessageHandledEvent;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class DispatchCollectedEnvelopesSubscriber implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector $delayedEnvelopesCollector
     * @param \Symfony\Component\Messenger\MessageBusInterface $messageBus
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly DelayedEnvelopesCollector $delayedEnvelopesCollector,
        protected readonly MessageBusInterface $messageBus,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function handleCollectedMessageEnvelopes(): void
    {
        foreach ($this->delayedEnvelopesCollector->popEnvelopes() as $envelope) {
            $this->redispatchEnvelopeIgnoringMailerException($envelope);
        }
    }

    public function resetCollectedMessageEnvelopes(): void
    {
        $this->delayedEnvelopesCollector->resetEnvelopes();
    }

    /**
     * @param \Symfony\Component\Messenger\Envelope $envelope
     */
    protected function redispatchEnvelopeIgnoringMailerException(Envelope $envelope): void
    {
        try {
            $this->messageBus->dispatch($envelope);
        } catch (HandlerFailedException $e) {
            foreach ($e->getNestedExceptions() as $nested) {
                if ($nested instanceof TransportExceptionInterface) {
                    $this->logger->error('There was a failure while sending emails', [
                        'exception' => $nested,
                    ]);

                    return;
                }
            }

            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => 'resetCollectedMessageEnvelopes',
            KernelEvents::RESPONSE => ['handleCollectedMessageEnvelopes', -10],
            ConsoleEvents::TERMINATE => ['handleCollectedMessageEnvelopes', -10],
            WorkerMessageHandledEvent::class => ['handleCollectedMessageEnvelopes', 10],
        ];
    }
}
