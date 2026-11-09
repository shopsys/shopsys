<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class DispatchAllProductsMessageHandler
{
    protected const int PRODUCT_IDS_BATCH_SIZE = 10000;

    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(DispatchAllProductsMessage $message): void
    {
        $productIds = $this->productFacade->iterateAllProductIdsExceptVariant();
        $productIdsToDispatch = [];

        foreach ($productIds as $productId) {
            $productIdsToDispatch[] = $productId['id'];

            if (count($productIdsToDispatch) < static::PRODUCT_IDS_BATCH_SIZE) {
                continue;
            }

            $this->dispatchProductIds($productIdsToDispatch, $message->exportScopes);
            $productIdsToDispatch = [];
        }

        if ($productIdsToDispatch === []) {
            return;
        }

        $this->dispatchProductIds($productIdsToDispatch, $message->exportScopes);
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     */
    protected function dispatchProductIds(array $productIds, array $exportScopes): void
    {
        $this->productRecalculationDispatcher->dispatchProductIds(
            $productIds,
            ProductRecalculationPriorityEnum::REGULAR,
            $exportScopes,
        );
    }
}
