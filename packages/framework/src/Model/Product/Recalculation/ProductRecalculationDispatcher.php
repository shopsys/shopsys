<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return int[]
     */
    public function dispatchProducts(array $products): array
    {
        return $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
        );
    }

    /**
     * @param int[] $productIds
     * @return int[]
     */
    public function dispatchProductIds(array $productIds): array
    {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $this->messageBus->dispatch(new ProductRecalculationMessage((int)$productId));
        }

        return $productIds;
    }

    /**
     * @param int $productId
     */
    public function dispatchSingleProductId(int $productId): void
    {
        $this->dispatchProductIds([$productId]);
    }

    public function dispatchAllProducts(): void
    {
        $this->messageBus->dispatch(new DispatchAllProductsMessage());
    }
}
