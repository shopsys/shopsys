<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;

/**
 * Decorates the handlers locator so that envelopes dispatched by a handler are confirmed in the collector
 * once the handler finishes successfully. Envelopes of a failing handler stay unconfirmed and are dropped
 * when the worker fails the message, while the confirmed ones are still sent.
 * Symfony retries only the failed handlers of a message (successful ones are skipped thanks to HandledStamp),
 * so envelopes dispatched by a successful handler must be sent even when a sibling handler fails - otherwise
 * they would be lost as nothing would dispatch them again.
 */
class SegmentedHandlersLocator implements HandlersLocatorInterface
{
    public function __construct(
        protected readonly HandlersLocatorInterface $handlersLocator,
        protected readonly DelayedEnvelopesCollector $delayedEnvelopesCollector,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getHandlers(Envelope $envelope): iterable
    {
        foreach ($this->handlersLocator->getHandlers($envelope) as $handlerDescriptor) {
            yield $this->getSegmentedHandlerDescriptor($handlerDescriptor);
        }
    }

    protected function getSegmentedHandlerDescriptor(HandlerDescriptor $handlerDescriptor): HandlerDescriptor
    {
        // batch handlers are detected by Symfony from the handler object itself, wrapping them would disable batching
        if ($handlerDescriptor->getBatchHandler() !== null) {
            return $handlerDescriptor;
        }

        $handler = $handlerDescriptor->getHandler();
        $collector = $this->delayedEnvelopesCollector;

        return new HandlerDescriptor(
            static function (object $message, mixed ...$arguments) use ($handler, $collector): mixed {
                $result = $handler($message, ...$arguments);
                $collector->confirmEnvelopes();

                return $result;
            },
            [
                // the name of the closure is just "Closure", the alias keeps the original handler identifiable
                // in HandledStamp so already handled handlers are still skipped on retry
                'alias' => $handlerDescriptor->getName(),
                'from_transport' => $handlerDescriptor->getOption('from_transport'),
            ],
        );
    }
}
