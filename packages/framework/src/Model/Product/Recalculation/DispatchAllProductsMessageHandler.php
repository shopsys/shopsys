<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Model\Product\ProductFacade;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DispatchAllProductsMessageHandler implements MessageHandlerInterface
{
    public function __construct(
        protected readonly ProductFacade $productFacade,
        protected readonly ProductRecalculationDispatcher $productRecalculationDispatcher,
    ) {
    }

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
