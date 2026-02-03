<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Nette\Utils\Json;
use Psr\Log\LoggerInterface;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class DispatchProductIdsBatchMessageHandler
{
    public function __construct(
        protected readonly ProductRepository $productRepository,
        protected readonly ProductRecalculationDispatcherExecutor $productRecalculationDispatcherExecutor,
        protected readonly LoggerInterface $logger,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(DispatchProductIdsBatchMessage $message): void
    {
        $dispatchedProductIds = $this->productRecalculationDispatcherExecutor->dispatchProductIds(
            $message->productIds,
            $message->productRecalculationPriorityEnum,
            $message->exportScopes,
        );

        if ($dispatchedProductIds === []) {
            $this->logger->info('No new products were dispatched as they are already in the queue.', [
                'product_ids' => Json::encode($message->productIds),
                'export_scopes' => Json::encode($message->exportScopes),
            ]);

            return;
        }

        $this->logger->info('Dispatched batch of products for recalculation.', [
            'product_ids' => Json::encode($dispatchedProductIds),
            'export_scopes' => Json::encode($message->exportScopes),
        ]);
    }
}
