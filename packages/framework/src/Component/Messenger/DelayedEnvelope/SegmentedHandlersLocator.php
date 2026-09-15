<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocatorInterface;
use Throwable;

/**
 * Reports the boundaries of every handler call to DelayedEnvelopesCollector, so the envelopes dispatched by a handler
 * are confirmed when it succeeds and discarded when it fails, independently of the other handlers of the same message.
 * Symfony retries only the failed handlers of a message (the successful ones are skipped thanks to HandledStamp),
 * so the envelopes of a successful handler must be sent even when a sibling handler fails - nothing would dispatch them again.
 *
 * Symfony 7.4 offers no hook around a single handler call, hence the decoration of the handlers locator and wrapping
 * of the handlers into closures. Symfony 8.2 dispatches HandlerStartingEvent, HandlerSuccessEvent and HandlerFailureEvent
 * around every handler call (https://github.com/symfony/symfony/pull/52425). After the upgrade, replace this class
 * and its decoration in services.yaml with an event subscriber calling the same DelayedEnvelopesCollector methods.
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
        // Symfony detects batch handlers from the handler object itself, wrapping them would disable batching.
        // Their envelopes stay outside of any handler segment: sent when the message succeeds, dropped when it fails.
        // Symfony 8.2 events cover batch handlers too (reported at flush time), so this exception disappears with them.
        if ($handlerDescriptor->getBatchHandler() !== null) {
            return $handlerDescriptor;
        }

        $handler = $handlerDescriptor->getHandler();
        $handlerName = $handlerDescriptor->getName();
        $collector = $this->delayedEnvelopesCollector;

        return new HandlerDescriptor(
            static function (object $message, mixed ...$arguments) use ($handler, $handlerName, $collector): mixed {
                $collector->startHandler($handlerName);

                try {
                    $result = $handler($message, ...$arguments);
                } catch (Throwable $exception) {
                    $collector->discardHandler($handlerName);

                    throw $exception;
                }

                $collector->confirmHandler($handlerName);

                return $result;
            },
            [
                // the name of the closure is just "Closure", the alias keeps the original handler identifiable
                // in HandledStamp so already handled handlers are still skipped on retry
                'alias' => $handlerName,
            ],
        );
    }
}
