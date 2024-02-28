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
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProducts(
        array $products,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        return $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $productRecalculationPriorityEnum,
            $exportScopes,
        );
    }

    /**
     * @param int[] $productIds
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $message = match ($productRecalculationPriorityEnum) {
                ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId, $exportScopes),
                ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId, $exportScopes),
            };
            $this->messageBus->dispatch($message);
        }

        return $productIds;
    }

    /**
     * @param int $productId
     * @param \Shopsys\FrameworkBundle\Model\Product\Recalculation\ProductRecalculationPriorityEnum $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     */
    public function dispatchSingleProductId(
        int $productId,
        ProductRecalculationPriorityEnumInterface $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriorityEnum, $exportScopes);
    }

    /**
     * @param string[] $exportScopes
     */
    public function dispatchAllProducts(array $exportScopes = []): void
    {
        $this->messageBus->dispatch(new DispatchAllProductsMessage($exportScopes));
    }
}
