<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class DispatchAllProductsMessageHandler
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    #[AsMessageHandler]
    public function __invoke(DispatchAllProductsMessage $message): void
    {
        $productIds = $this->productFacade->iterateAllProductIdsExceptVariant();

        foreach ($productIds as $productId) {
            $this->productRecalculationDispatcher->dispatchSingleProductId(
                $productId['id'],
                ProductRecalculationPriorityEnum::REGULAR,
                $message->exportScopes,
            );
        }
    }
}
