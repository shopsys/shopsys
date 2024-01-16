<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     * @return int[]
     */
    public function dispatchProducts(
        array $products,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
    ): array {
        return $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $productRecalculationPriorityEnum,
        );
    }

    /**
     * @param int[] $productIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
    ): array {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $message = match ($productRecalculationPriorityEnum) {
                ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId),
                ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId),
            };
            $this->messageBus->dispatch($message);
        }

        return $productIds;
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     */
    public function dispatchSingleProductId(
        int $productId,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriorityEnum);
    }

    public function dispatchAllProducts(): void
    {
        $this->messageBus->dispatch(new DispatchAllProductsMessage());
    }
}
