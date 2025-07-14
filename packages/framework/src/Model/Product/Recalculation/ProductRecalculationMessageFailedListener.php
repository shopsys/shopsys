<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Override;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerMessageFailedEvent;

class ProductRecalculationMessageFailedListener implements EventSubscriberInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade
     * @param \Psr\Log\LoggerInterface $monologQueueLogger
     */
    public function __construct(
        protected readonly ProductRecalculationDeduplicationFacade $productRecalculationDeduplicationFacade,
        protected readonly LoggerInterface $monologQueueLogger,
    ) {
    }

    /**
     * @return array
     */
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            WorkerMessageFailedEvent::class => 'onMessageFailed',
        ];
    }

    /**
     * @param \Symfony\Component\Messenger\Event\WorkerMessageFailedEvent $event
     */
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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage $message
     * @return string
     */
    protected function getPriority(AbstractProductRecalculationMessage $message): string
    {
        return match (true) {
            $message instanceof ProductRecalculationPriorityHighMessage => ProductRecalculationPriorityEnum::HIGH,
            $message instanceof ProductRecalculationPriorityRegularMessage => ProductRecalculationPriorityEnum::REGULAR,
            default => throw new UnknownProductRecalculationPriorityException($message::class),
        };
    }
}
