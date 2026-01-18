<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\Acknowledger;
use Symfony\Component\Messenger\Handler\BatchHandlerInterface;
use Symfony\Component\Messenger\Handler\BatchHandlerTrait;
use Throwable;

class ProductRecalculationMessageHandler implements BatchHandlerInterface
{
    use BatchHandlerTrait;

    public function __construct(
        protected readonly ProductRecalculationFacade $productRecalculationFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(AbstractProductRecalculationMessage $message, ?Acknowledger $ack = null): mixed
    {
        return $this->handle($message, $ack);
    }

    /**
     * @param array<int, array{0: \Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage, 1: \Symfony\Component\Messenger\Handler\Acknowledger}> $jobs
     */
    protected function process(array $jobs): void
    {
        $priorities = [];

        /**
         * @var \Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage $message
         * @var \Symfony\Component\Messenger\Handler\Acknowledger $ack
         */
        foreach ($jobs as [$message, $ack]) {
            $priority = $this->getPriority($message);
            $priorities[$priority]['productIds'][] = $message->productId;
            $priorities[$priority]['acknowledgers'][] = $ack;
        }

        foreach ($priorities as $priority => $data) {
            $this->processPriority($data['productIds'], $priority, $data['acknowledgers']);
        }
    }

    /**
     * @param int[] $productIds
     * @param \Symfony\Component\Messenger\Handler\Acknowledger[] $acknowledgers
     */
    protected function processPriority(array $productIds, string $priority, array $acknowledgers): void
    {
        if (count($productIds) === 0) {
            return;
        }

        try {
            $this->productRecalculationFacade->recalculate($productIds, $priority, $this->logger);
            $this->ackAll($acknowledgers);
        } catch (Throwable $e) {
            $this->logger->error('Products recalculation failed because of: ' . $e->getMessage(), [
                'exception' => $e,
                'productIds' => $productIds,
                'priority' => $priority,
            ]);
            $this->nackAll($acknowledgers, $e);
        }
    }

    protected function getPriority(AbstractProductRecalculationMessage $message): string
    {
        return match (true) {
            $message instanceof ProductRecalculationPriorityHighMessage => ProductRecalculationPriorityEnum::HIGH,
            $message instanceof ProductRecalculationPriorityRegularMessage => ProductRecalculationPriorityEnum::REGULAR,
            default => throw new UnknownProductRecalculationPriorityException($message::class),
        };
    }

    /**
     * @param \Symfony\Component\Messenger\Handler\Acknowledger[] $acknowledgers
     */
    protected function ackAll(array $acknowledgers): void
    {
        foreach ($acknowledgers as $acknowledger) {
            $acknowledger->ack();
        }
    }

    /**
     * @param \Symfony\Component\Messenger\Handler\Acknowledger[] $acknowledgers
     */
    protected function nackAll(array $acknowledgers, Throwable $e): void
    {
        foreach ($acknowledgers as $acknowledger) {
            $acknowledger->nack($e);
        }
    }
}
