<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\InvalidScopeException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig $productExportScopeConfig
     */
    public function __construct(
        protected readonly ProductExportScopeConfig $productExportScopeConfig,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProducts(
        array $products,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
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
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     * @return int[]
     */
    public function dispatchProductIds(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): array {
        $this->verifyExportScopes($exportScopes);
        $productIds = array_unique($productIds);

        foreach ($productIds as $productId) {
            $message = match ($productRecalculationPriorityEnum) {
                ProductRecalculationPriorityEnum::HIGH => new ProductRecalculationPriorityHighMessage((int)$productId, $exportScopes),
                ProductRecalculationPriorityEnum::REGULAR => new ProductRecalculationPriorityRegularMessage((int)$productId, $exportScopes),
                default => throw new UnknownProductRecalculationPriorityException($productRecalculationPriorityEnum),
            };
            $this->messageBus->dispatch($message);
        }

        return $productIds;
    }

    /**
     * @param int $productId
     * @param string $productRecalculationPriorityEnum
     * @param string[] $exportScopes
     */
    public function dispatchSingleProductId(
        int $productId,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->dispatchProductIds([$productId], $productRecalculationPriorityEnum, $exportScopes);
    }

    /**
     * @param string[] $exportScopes
     */
    public function dispatchAllProducts(array $exportScopes = []): void
    {
        $this->verifyExportScopes($exportScopes);
        $this->messageBus->dispatch(new DispatchAllProductsMessage($exportScopes));
    }

    /**
     * @param string[] $exportScopes
     */
    protected function verifyExportScopes(array $exportScopes): void
    {
        foreach ($exportScopes as $scope) {
            if (!in_array($scope, $this->productExportScopeConfig->getAllProductExportScopes(), true)) {
                throw new InvalidScopeException($scope);
            }
        }
    }
}
