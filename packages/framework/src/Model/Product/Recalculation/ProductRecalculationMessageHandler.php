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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationFacade $productRecalculationFacade
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        protected readonly ProductRecalculationFacade $productRecalculationFacade,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessage $message
     * @param \Symfony\Component\Messenger\Handler\Acknowledger|null $ack
     * @return mixed
     */
    public function __invoke(ProductRecalculationMessage $message, ?Acknowledger $ack = null): mixed
    {
        return $this->handle($message, $ack);
    }

    /**
     * @param array $jobs
     */
    protected function process(array $jobs): void
    {
        $productIds = [];

        /**
         * @var \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationMessage $message
         * @var \Symfony\Component\Messenger\Handler\Acknowledger $ack
         */
        foreach ($jobs as [$message, $ack]) {
            try {
                $productIds[] = $message->productId;
                $ack->ack($message->productId);
            } catch (Throwable $e) {
                $this->logger->error($e->getMessage());
                $ack->nack($e);
            }
        }

        $this->productRecalculationFacade->recalculate($productIds);
        $this->logger->info('Product recalculated', ['product_ids' => json_encode($productIds, JSON_THROW_ON_ERROR)]);
    }
}
