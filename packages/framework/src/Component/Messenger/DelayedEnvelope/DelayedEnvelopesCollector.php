<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Symfony\Component\Messenger\Envelope;

class DelayedEnvelopesCollector
{
    /**
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    protected array $delayedEnvelopes = [];

    /**
     * @param \Symfony\Component\Messenger\Envelope $envelope
     */
    public function addEnvelope(Envelope $envelope): void
    {
        $this->delayedEnvelopes[] = $envelope;
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function popEnvelopes(): array
    {
        $envelopes = $this->delayedEnvelopes;

        $this->resetEnvelopes();

        return $envelopes;
    }

    public function resetEnvelopes(): void
    {
        $this->delayedEnvelopes = [];
    }
}
