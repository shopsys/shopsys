<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Recalculation;

use Shopsys\FrameworkBundle\Component\Messenger\AbstractMessageDispatcher;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\Exception\InvalidScopeException;
use Shopsys\FrameworkBundle\Model\Product\Elasticsearch\Scope\ProductExportScopeConfig;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductRecalculationDispatcher extends AbstractMessageDispatcher
{
    protected const int DISPATCH_BATCH_SIZE = 2000;

    public function __construct(
        protected readonly ProductExportScopeConfig $productExportScopeConfig,
        protected readonly ProductRecalculationDispatcherExecutor $productRecalculationDispatcherExecutor,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @param string[] $exportScopes
     */
    public function dispatchProducts(
        array $products,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->dispatchProductIds(
            array_map(static fn (Product $product) => $product->getId(), $products),
            $productRecalculationPriorityEnum,
            $exportScopes,
        );
    }

    /**
     * @param int[] $productIds
     * @param string[] $exportScopes
     */
    public function dispatchProductIds(
        array $productIds,
        string $productRecalculationPriorityEnum = ProductRecalculationPriorityEnum::REGULAR,
        array $exportScopes = [],
    ): void {
        $this->verifyExportScopes($exportScopes);
        $productIds = array_unique($productIds);

        $productIdsCount = count($productIds);

        if ($productIdsCount <= static::DISPATCH_BATCH_SIZE) {
            $this->productRecalculationDispatcherExecutor->dispatchProductIds(
                $productIds,
                $productRecalculationPriorityEnum,
                $exportScopes,
            );

            return;
        }

        for ($i = 0; $i < $productIdsCount; $i += static::DISPATCH_BATCH_SIZE) {
            $productIdsBatch = array_slice($productIds, $i, static::DISPATCH_BATCH_SIZE);

            $this->messageBus->dispatch(
                new DispatchProductIdsBatchMessage(
                    $productIdsBatch,
                    $exportScopes,
                    $productRecalculationPriorityEnum,
                ),
            );
        }
    }

    /**
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
