<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope;

use Symfony\Component\Messenger\Envelope;

class DelayedEnvelopesCollector
{
    /**
     * Envelopes dispatched outside of any running handler
     *
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    protected array $delayedEnvelopes = [];

    /**
     * Envelopes dispatched by handlers that already succeeded
     *
     * @var \Symfony\Component\Messenger\Envelope[]
     */
    protected array $confirmedEnvelopes = [];

    /**
     * Envelopes dispatched by handlers whose outcome is not known yet, keyed by handler name
     *
     * @var array<string, \Symfony\Component\Messenger\Envelope[]>
     */
    protected array $envelopesByRunningHandler = [];

    /**
     * Names of the running handlers, the last one receives the dispatched envelopes
     *
     * @var string[]
     */
    protected array $runningHandlerNames = [];

    public function addEnvelope(Envelope $envelope): void
    {
        $runningHandlerName = end($this->runningHandlerNames);

        if ($runningHandlerName === false) {
            $this->delayedEnvelopes[] = $envelope;

            return;
        }

        $this->envelopesByRunningHandler[$runningHandlerName][] = $envelope;
    }

    public function startHandler(string $handlerName): void
    {
        $this->envelopesByRunningHandler[$handlerName] ??= [];
        $this->runningHandlerNames[] = $handlerName;
    }

    /**
     * The envelopes dispatched by the handler will be sent even if another handler of the same message fails
     */
    public function confirmHandler(string $handlerName): void
    {
        array_push($this->confirmedEnvelopes, ...($this->envelopesByRunningHandler[$handlerName] ?? []));

        $this->finishHandler($handlerName);
    }

    /**
     * The envelopes dispatched by the handler are dropped, the handler dispatches them again when it is retried
     */
    public function discardHandler(string $handlerName): void
    {
        $this->finishHandler($handlerName);
    }

    protected function finishHandler(string $handlerName): void
    {
        unset($this->envelopesByRunningHandler[$handlerName]);

        $position = array_search($handlerName, $this->runningHandlerNames, true);

        if ($position === false) {
            return;
        }

        unset($this->runningHandlerNames[$position]);
        $this->runningHandlerNames = array_values($this->runningHandlerNames);
    }

    /**
     * @return \Symfony\Component\Messenger\Envelope[]
     */
    public function popEnvelopes(): array
    {
        $envelopes = [...$this->confirmedEnvelopes, ...$this->delayedEnvelopes];

        foreach ($this->envelopesByRunningHandler as $handlerEnvelopes) {
            array_push($envelopes, ...$handlerEnvelopes);
        }

        $this->resetEnvelopes();

        return $envelopes;
    }

    /**
     * Envelopes of the handlers that did not succeed are dropped
     *
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
        $this->envelopesByRunningHandler = [];
        $this->runningHandlerNames = [];
    }
}
