<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductStockFacade
{
    public function __construct(
        protected readonly ProductStockRepository $productStockRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly ProductStockFactory $productStockFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStocksByProduct($product);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductIndexedByStockId(Product $product): array
    {
        $productStocks = $this->getProductStocksByProduct($product);
        $productStocksById = [];

        foreach ($productStocks as $productStock) {
            $productStocksById[$productStock->getStock()->getId()] = $productStock;
        }

        return $productStocksById;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainIdIndexedByStockId(Product $product, int $domainId): array
    {
        $productStocksById = [];

        foreach ($this->getProductStocksByProductIndexedByStockId($product) as $id => $productStock) {
            if ($productStock->getStock()->isEnabled($domainId)) {
                $productStocksById[$id] = $productStock;
            }
        }

        return $productStocksById;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock[] $stocksIndexedById
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockData[] $productStockDataItems
     */
    public function editProductStockRelations(
        Product $product,
        array $stocksIndexedById,
        array $productStockDataItems,
    ): void {
        $productStocksIndexedByStockId = $this->productStockRepository->getProductStocksByStocksAndProductIndexedByStockId(
            array_keys($stocksIndexedById),
            $product,
        );

        foreach ($stocksIndexedById as $stockId => $stock) {
            $filteredProductStockDataItem = array_filter($productStockDataItems, fn ($productStockDataItem) => $productStockDataItem->stockId === $stockId);
            $productStockData = array_first($filteredProductStockDataItem);

            $productStock = $productStocksIndexedByStockId[$stockId] ?? $this->createProductStock($product, $stock);
            $productStock->edit($productStockData);
        }

        $this->em->flush();
    }

    protected function createProductStock(Product $product, Stock $stock): ProductStock
    {
        $productStock = $this->productStockFactory->create($stock, $product);
        $this->em->persist($productStock);

        return $productStock;
    }

    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        return $this->productStockRepository->isProductAvailableOnDomain($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $products
     * @return array<int, int>
     */
    public function getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
        array $products,
        int $domainId,
    ): array {
        return $this->productStockRepository->getGroupedStockQuantitiesByProductsAndDomainIdIndexedByProductId(
            $products,
            $domainId,
        );
    }

    public function createProductStockRelationForStockId(int $stockId): void
    {
        $this->productStockRepository->createProductStockRelationForStockId($stockId);
    }
}
