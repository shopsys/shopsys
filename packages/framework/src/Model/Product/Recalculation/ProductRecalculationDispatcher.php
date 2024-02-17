<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * TODO remove - usunsued after removing deprecated properties from product entity
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
     * @param string[] $affectedPropertyNames
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $scopes = [],
    ): array {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $message = match ($productRecalculationPriorityEnum) {
                ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId, $scopes),
                ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId, $scopes),
            };
            $this->messageBus->dispatch($message);
        }

        return $productIds;
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     * @param string[] $affectedPropertyNames
     */
    public function dispatchSingleProductId(
        int $productId,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $scopes = [],
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriorityEnum, $scopes);
    }

    public function dispatchAllProducts(): void
    {
        $this->messageBus->dispatch(new DispatchAllProductsMessage());
    }
}
