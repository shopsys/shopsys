<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $productRecalculationPriority
     * @return int[]
     */
    public function dispatchProducts(
        array $products,
        string $productRecalculationPriority = ProductRecalculationPriority::REGULAR,
    ): array {
        return $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $productRecalculationPriority,
        );
    }

    /**
     * @param int[] $productIds
     * @param string $productRecalculationPriority
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        string $productRecalculationPriority = ProductRecalculationPriority::REGULAR,
    ): array {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $message = match ($productRecalculationPriority) {
                ProductRecalculationPriority::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId),
                ProductRecalculationPriority::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId),
                default => throw new UnknownProductRecalculationPriorityException($productRecalculationPriority),
            };
            $this->messageBus->dispatch($message);
        }

        return $productIds;
    }

    /**
     * @param int $productId
     * @param string $productRecalculationPriority
     */
    public function dispatchSingleProductId(
        int $productId,
        string $productRecalculationPriority = ProductRecalculationPriority::REGULAR,
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriority);
    }

    public function dispatchAllProducts(): void
    {
        $this->messageBus->dispatch(new DispatchAllProductsMessage());
    }
}
