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
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    protected array $confirmedEnvelopes = [];

    public function addEnvelope(Envelope $envelope): void
    {
        $this->delayedEnvelopes[] = $envelope;
    }

    public function confirmEnvelopes(): void
    {
        array_push($this->confirmedEnvelopes, ...$this->delayedEnvelopes);
        $this->delayedEnvelopes = [];
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function popEnvelopes(): array
    {
        $envelopes = [...$this->confirmedEnvelopes, ...$this->delayedEnvelopes];

        $this->resetEnvelopes();

        return $envelopes;
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function popConfirmedEnvelopes(): array
    {
        $envelopes = $this->confirmedEnvelopes;

        $this->resetEnvelopes();

        return $envelopes;
    }

    public function resetEnvelopes(): void
    {
        $this->delayedEnvelopes = [];
        $this->confirmedEnvelopes = [];
    }
}
