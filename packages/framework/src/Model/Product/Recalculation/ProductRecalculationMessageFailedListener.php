<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class ProductRecalculationMessageFailedListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade,
        protected readonly LoggerInterface $monologQueueLogger,
    ) {
    }

    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    public function onMessageFailed(WorkerMessageFailedEvent $event): void
    {
        $envelope = $event->getEnvelope();
        $message = $envelope->getMessage();

        if (!$message instanceof AbstractProductRecalculationMessage) {
            return;
        }

        if ($event->willRetry()) {
            return;
        }

        $this->productRecalculationDeduplicationFacade->delete([$message->productId], $this->getPriority($message));

        $this->monologQueueLogger->info(
            sprintf(
                'Deleted deduplication lock for unprocessable message "%s" of product ID "%d"',
                get_class($message),
                $message->productId,
            ),
            ['productId' => $message->productId],
        );
    }

    protected function getPriority(AbstractProductRecalculationMessage $message): string
    {
        return match (true) {
            $message instanceof ProductRecalculationPriorityHighMessage => ProductRecalculationPriorityEnum::HIGH,
            $message instanceof ProductRecalculationPriorityRegularMessage => ProductRecalculationPriorityEnum::REGULAR,
            default => throw new UnknownProductRecalculationPriorityException($message::class),
        };
    }
}
