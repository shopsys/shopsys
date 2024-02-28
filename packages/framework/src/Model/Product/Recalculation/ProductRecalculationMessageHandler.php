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
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage $message
     * @param \Symfony\Component\Messenger\Handler\Acknowledger|null $ack
     * @return mixed
     */
    public function __invoke(AbstractProductRecalculationMessage $message, ?Acknowledger $ack = null): mixed
    {
        return $this->handle($message, $ack);
    }

    /**
     * @param array $jobs
     */
    protected function process(array $jobs): void
    {
        $acknowledgers = [];
        $exportScopesIndexedByProductId = [];

        /**
         * @var \Shopsys\FrameworkBundle\Model\Product\Recalculation\AbstractProductRecalculationMessage $message
         * @var \Symfony\Component\Messenger\Handler\Acknowledger $ack
         */
        foreach ($jobs as [$message, $ack]) {
            $acknowledgers[] = $ack;
            $exportScopesIndexedByProductId[$message->productId] = $message->exportScopes;
        }

        try {
            $this->productRecalculationFacade->recalculate($exportScopesIndexedByProductId);
            $this->ackAll($acknowledgers);
        } catch (Throwable $e) {
            $this->logger->error($e->getMessage());
            $this->nackAll($acknowledgers, $e);

            return;
        }

        $this->logger->info('Product recalculated', ['product_ids' => json_encode(array_keys($exportScopesIndexedByProductId), JSON_THROW_ON_ERROR)]);
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
     * @param \Throwable $e
     */
    protected function nackAll(array $acknowledgers, Throwable $e): void
    {
        foreach ($acknowledgers as $acknowledger) {
            $acknowledger->nack($e);
        }
    }
}
