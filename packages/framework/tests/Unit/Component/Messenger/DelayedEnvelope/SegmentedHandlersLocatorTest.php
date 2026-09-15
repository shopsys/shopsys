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
        $originalDescriptor = new HandlerDescriptor($originalHandler);

        [$segmentedDescriptor] = $this->getSegmentedDescriptors($originalDescriptor);

        $this->assertSame('Closure@' . $originalDescriptor->getName(), $segmentedDescriptor->getName());
    }

    public function testEnvelopesDispatchedBySuccessfulHandlerAreConfirmed(): void
    {
        $dispatchedEnvelope = new Envelope(new stdClass());
        $collector = $this->collector;
        $originalDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector, $dispatchedEnvelope): string {
            $collector->addEnvelope($dispatchedEnvelope);

            return 'result';
        });

        [$segmentedDescriptor] = $this->getSegmentedDescriptors($originalDescriptor);
        $result = $segmentedDescriptor->getHandler()(new stdClass());

        $this->assertSame('result', $result);
        $this->assertSame([$dispatchedEnvelope], $this->collector->popConfirmedEnvelopes());
    }

    public function testEnvelopesDispatchedByFailingHandlerAreDiscardedAndExceptionIsPropagated(): void
    {
        $collector = $this->collector;
        $originalDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector): void {
            $collector->addEnvelope(new Envelope(new stdClass()));

            throw new RuntimeException('handler failed');
        });
        [$segmentedDescriptor] = $this->getSegmentedDescriptors($originalDescriptor);

        try {
            $segmentedDescriptor->getHandler()(new stdClass());
            $this->fail('The exception of the handler must be propagated');
        } catch (RuntimeException $exception) {
            $this->assertSame('handler failed', $exception->getMessage());
        }

        $this->assertSame([], $this->collector->popEnvelopes());
    }

    public function testOnlyEnvelopesOfSuccessfulHandlerAreConfirmedWhenEarlierSiblingFails(): void
    {
        $collector = $this->collector;
        $envelopeOfSuccessfulHandler = new Envelope(new stdClass());
        $failingDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector): void {
            $collector->addEnvelope(new Envelope(new stdClass()));

            throw new RuntimeException('handler failed');
        }, ['alias' => 'failing']);
        $successfulDescriptor = new HandlerDescriptor(static function (stdClass $message) use ($collector, $envelopeOfSuccessfulHandler): void {
            $collector->addEnvelope($envelopeOfSuccessfulHandler);
        }, ['alias' => 'successful']);

        [$segmentedFailingDescriptor, $segmentedSuccessfulDescriptor] = $this->getSegmentedDescriptors($failingDescriptor, $successfulDescriptor);

        // HandleMessageMiddleware keeps calling the remaining handlers after one of them fails
        try {
            $segmentedFailingDescriptor->getHandler()(new stdClass());
        } catch (RuntimeException) {
        }
        $segmentedSuccessfulDescriptor->getHandler()(new stdClass());

        $this->assertSame([$envelopeOfSuccessfulHandler], $this->collector->popConfirmedEnvelopes());
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

        [$segmentedDescriptor] = $this->getSegmentedDescriptors($originalDescriptor);

        $this->assertSame($originalDescriptor, $segmentedDescriptor);
    }

    /**
     * @return \Symfony\Component\Messenger\Handler\HandlerDescriptor[]
     */
    private function getSegmentedDescriptors(HandlerDescriptor ...$originalDescriptors): array
    {
        $locator = new SegmentedHandlersLocator(
            new HandlersLocator([stdClass::class => $originalDescriptors]),
            $this->collector,
        );
        $descriptors = [...$locator->getHandlers(new Envelope(new stdClass()))];
        $this->assertCount(count($originalDescriptors), $descriptors);

        return $descriptors;
    }
}
