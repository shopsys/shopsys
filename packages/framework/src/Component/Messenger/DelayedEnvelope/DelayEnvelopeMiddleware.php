<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Override;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class DelayEnvelopeMiddleware implements MiddlewareInterface
{
    public function __construct(
        protected readonly DelayedEnvelopesCollector $delayedEnvelopesCollector,
    ) {
    }

    #[Override]
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
