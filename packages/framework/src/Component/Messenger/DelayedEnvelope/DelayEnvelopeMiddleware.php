<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class DelayEnvelopeMiddleware implements MiddlewareInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector $delayedEnvelopesCollector
     */
    public function __construct(
        protected readonly DelayedEnvelopesCollector $delayedEnvelopesCollector,
    ) {
    }

    /**
     * @param \Symfony\Component\Messenger\Envelope $envelope
     * @param \Symfony\Component\Messenger\Middleware\StackInterface $stack
     * @return \Symfony\Component\Messenger\Envelope
     */
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if ($envelope->last(DelayedEnvelopeStamp::class) !== null || count($envelope->all(ReceivedStamp::class)) > 0) {
            return $stack->next()->handle($envelope, $stack);
        }

        $envelope = $envelope->with(new DelayedEnvelopeStamp());

        $this->delayedEnvelopesCollector->addEnvelope($envelope);

        return $envelope;
    }
}
