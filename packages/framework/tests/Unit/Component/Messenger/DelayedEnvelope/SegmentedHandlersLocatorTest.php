<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Component\Messenger\DelayedEnvelope;

use Override;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\DelayedEnvelopesCollector;
use Shopsys\FrameworkBundle\Component\Messenger\DelayedEnvelope\SegmentedHandlersLocator;
use stdClass;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\Acknowledger;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;
use Symfony\Component\Messenger\Handler\HandlerDescriptor;
use Symfony\Component\Messenger\Handler\HandlersLocator;

class SegmentedHandlersLocatorTest extends TestCase
{
    private DelayedEnvelopesCollector $collector;

    #[Override]
    protected function setUp(): void
    {
        $this->collector = new DelayedEnvelopesCollector();
    }

    public function testHandlerNameKeepsOriginalHandlerIdentifiable(): void
    {
        $originalHandler = new class() {
            public function __invoke(stdClass $message): void
            {
            }
        };
        $originalDescriptor = new HandlerDescriptor($originalHandler, ['from_transport' => 'my_transport']);

        $segmentedDescriptor = $this->getSegmentedDescriptor($originalDescriptor);

        $this->assertSame('Closure@' . $originalDescriptor->getName(), $segmentedDescriptor->getName());
        $this->assertSame('my_transport', $segmentedDescriptor->getOption('from_transport'));
    }

    public function testEnvelopesDispatchedBySuccessfulHandlerAreConfirmed(): void
    {
        $dispatchedEnvelope = new Envelope(new stdClass());
        $collector = $this->collector;
        $originalDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector, $dispatchedEnvelope): string {
            $collector->addEnvelope($dispatchedEnvelope);

            return 'result';
        });

        $result = $this->getSegmentedDescriptor($originalDescriptor)->getHandler()(new stdClass());

        $this->assertSame('result', $result);
        $this->assertSame([$dispatchedEnvelope], $this->collector->popConfirmedEnvelopes());
    }

    public function testEnvelopesDispatchedByFailingHandlerAreNotConfirmed(): void
    {
        $envelopeOfPreviousHandler = new Envelope(new stdClass());
        $this->collector->addEnvelope($envelopeOfPreviousHandler);
        $this->collector->confirmEnvelopes();
        $collector = $this->collector;
        $originalDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector): void {
            $collector->addEnvelope(new Envelope(new stdClass()));

            throw new RuntimeException('handler failed');
        });
        $segmentedHandler = $this->getSegmentedDescriptor($originalDescriptor)->getHandler();

        try {
            $segmentedHandler(new stdClass());
            $this->fail('The exception of the handler must be propagated');
        } catch (RuntimeException $exception) {
            $this->assertSame('handler failed', $exception->getMessage());
        }

        $this->assertSame([$envelopeOfPreviousHandler], $this->collector->popConfirmedEnvelopes());
    }

    public function testBatchHandlerIsNotWrapped(): void
    {
        $batchHandler = new class() implements BatchHandlerInterface {
            public function __invoke(stdClass $message, ?Acknowledger $ack = null): int
            {
                return 0;
            }

            public function flush(bool $force): void
            {
            }
        };
        $originalDescriptor = new HandlerDescriptor($batchHandler);

        $segmentedDescriptor = $this->getSegmentedDescriptor($originalDescriptor);

        $this->assertSame($originalDescriptor, $segmentedDescriptor);
    }

    private function getSegmentedDescriptor(HandlerDescriptor $originalDescriptor): HandlerDescriptor
    {
        $locator = new SegmentedHandlersLocator(
            new HandlersLocator([stdClass::class => [$originalDescriptor]]),
            $this->collector,
        );
        $descriptors = [...$locator->getHandlers(new Envelope(new stdClass()))];
        $this->assertCount(1, $descriptors);

        return $descriptors[0];
    }
}
