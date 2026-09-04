<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger\DelayedEnvelope;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use stdClass;
use Symfony\Component\Messenger\Envelope;

class DelayedEnvelopesCollectorTest extends TestCase
{
    public function testPopEnvelopesReturnsConfirmedEnvelopesFirst(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $confirmedEnvelope = new Envelope(new stdClass());
        $pendingEnvelope = new Envelope(new stdClass());
        $collector->addEnvelope($confirmedEnvelope);
        $collector->confirmEnvelopes();
        $collector->addEnvelope($pendingEnvelope);

        $this->assertSame([$confirmedEnvelope, $pendingEnvelope], $collector->popEnvelopes());
        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testPopConfirmedEnvelopesDropsPendingEnvelopes(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $confirmedEnvelope = new Envelope(new stdClass());
        $collector->addEnvelope($confirmedEnvelope);
        $collector->confirmEnvelopes();
        $collector->addEnvelope(new Envelope(new stdClass()));

        $this->assertSame([$confirmedEnvelope], $collector->popConfirmedEnvelopes());
        $this->assertSame([], $collector->popEnvelopes());
    }

    public function testResetDropsBothConfirmedAndPendingEnvelopes(): void
    {
        $collector = new DelayedEnvelopesCollector();
        $collector->addEnvelope(new Envelope(new stdClass()));
        $collector->confirmEnvelopes();
        $collector->addEnvelope(new Envelope(new stdClass()));

        $collector->resetEnvelopes();

        $this->assertSame([], $collector->popEnvelopes());
    }
}
