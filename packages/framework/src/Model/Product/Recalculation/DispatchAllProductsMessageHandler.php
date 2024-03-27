<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DispatchAllProductsMessageHandler implements MessageHandlerInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\ProductFacade $productFacade
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationDispatcher $productRecalculationDispatcher
     */
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\DispatchAllProductsMessage $message
     */
    public function __invoke(DispatchAllProductsMessage $message): void
    {
        $productIds = $this->productFacade->iterateAllProductIds();

        foreach ($productIds as $productId) {
            $this->productRecalculationDispatcher->dispatchSingleProductId($productId['id'], ProductRecalculationPriorityEnum::REGULAR, $message->exportScopes);
        }
    }
}
