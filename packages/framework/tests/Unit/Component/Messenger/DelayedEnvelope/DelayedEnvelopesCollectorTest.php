<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger\DelayedEnvelope;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use stdClass;
use Symfony\Component\Messenger\Envelope;

class DelayedEnvelopesCollectorTest extends TestCase
{
    public function testEnvelopesOutsideOfHandlersArePopped(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $envelope = new Envelope(new stdClass());
        $collector->addEnvelope($envelope);

        $this->assertSame([$envelope], $collector->popEnvelopes());
        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testEnvelopesOfConfirmedHandlerAreConfirmed(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $envelope = new Envelope(new stdClass());
        $collector->startHandler('handler');
        $collector->addEnvelope($envelope);
        $collector->confirmHandler('handler');

        $this->assertSame([$envelope], $collector->popConfirmedEnvelopes());
    }

    public function testEnvelopesOfDiscardedHandlerAreDropped(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $collector->startHandler('handler');
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->discardHandler('handler');

        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testOnlyEnvelopesOfSuccessfulHandlerAreConfirmedWhenSiblingFails(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $envelopeOfSuccessfulHandler = new Envelope(new stdClass());

        $collector->startHandler('failing');
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->discardHandler('failing');

        $collector->startHandler('successful');
        $collector->addEnvelope($envelopeOfSuccessfulHandler);
        $collector->confirmHandler('successful');

        $this->assertSame([$envelopeOfSuccessfulHandler], $collector->popConfirmedEnvelopes());
    }

    public function testEnvelopesAreAttributedToTheLastStartedHandler(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $outerEnvelope = new Envelope(new stdClass());
        $innerEnvelope = new Envelope(new stdClass());

        $collector->startHandler('outer');
        $collector->addEnvelope($outerEnvelope);
        $collector->startHandler('inner');
        $collector->addEnvelope($innerEnvelope);
        $collector->confirmHandler('inner');
        $collector->discardHandler('outer');

        $this->assertSame([$innerEnvelope], $collector->popConfirmedEnvelopes());
    }

    public function testHandlerFinishedOutOfOrderKeepsItsOwnEnvelopes(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $batchEnvelope = new Envelope(new stdClass());
        $regularEnvelope = new Envelope(new stdClass());

        // a batch handler reports its outcome at flush time, after later handlers already finished
        $collector->startHandler('batch');
        $collector->addEnvelope($batchEnvelope);
        $collector->startHandler('regular');
        $collector->addEnvelope($regularEnvelope);
        $collector->confirmHandler('regular');
        $collector->confirmHandler('batch');

        $this->assertSame([$regularEnvelope, $batchEnvelope], $collector->popConfirmedEnvelopes());
    }

    public function testPopConfirmedEnvelopesDropsPendingEnvelopes(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $confirmedEnvelope = new Envelope(new stdClass());
        $collector->startHandler('handler');
        $collector->addEnvelope($confirmedEnvelope);
        $collector->confirmHandler('handler');
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->startHandler('running');
        $collector->addEnvelope(new Envelope(new stdClass()));

        $this->assertSame([$confirmedEnvelope], $collector->popConfirmedEnvelopes());
        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testPopEnvelopesReturnsConfirmedPendingAndRunningHandlerEnvelopes(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $confirmedEnvelope = new Envelope(new stdClass());
        $pendingEnvelope = new Envelope(new stdClass());
        $runningHandlerEnvelope = new Envelope(new stdClass());
        $collector->startHandler('handler');
        $collector->addEnvelope($confirmedEnvelope);
        $collector->confirmHandler('handler');
        $collector->addEnvelope($pendingEnvelope);
        $collector->startHandler('running');
        $collector->addEnvelope($runningHandlerEnvelope);

        $this->assertSame([$confirmedEnvelope, $pendingEnvelope, $runningHandlerEnvelope], $collector->popEnvelopes());
        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testResetDropsEverything(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $collector->startHandler('handler');
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->confirmHandler('handler');
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->startHandler('running');
        $collector->addEnvelope(new Envelope(new stdClass()));

        $collector->resetEnvelopes();

        $this->assertSame([], $collector->popEnvelopes());
    }
}
