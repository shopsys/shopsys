<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Shopsys\FrameworkBundle\Model\Product\Product;

class ProductStockFacade
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Stock\ProductStockRepository $productStockRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        protected readonly ProductStockRepository $productStockRepository,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStocksByProduct($product);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
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
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
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
            $productStockData = reset($filteredProductStockDataItem);

            $productStock = $productStocksIndexedByStockId[$stockId] ?? $this->createProductStock($product, $stock);
            $productStock->edit($productStockData);
        }

        $this->em->flush();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param \Shopsys\FrameworkBundle\Model\Stock\Stock $stock
     * @return \Shopsys\FrameworkBundle\Model\Stock\ProductStock
     */
    protected function createProductStock(Product $product, Stock $stock): ProductStock
    {
        $productStock = new ProductStock($stock, $product);
        $this->em->persist($productStock);

        return $productStock;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product $product
     * @param int $domainId
     * @return bool
     */
    public function isProductAvailableOnDomain(Product $product, int $domainId): bool
    {
        return $this->productStockRepository->isProductAvailableOnDomain($product, $domainId);
    }

    /**
     * @param int $stockId
     */
    public function createProductStockRelationForStockId(int $stockId): void
    {
        $this->productStockRepository->createProductStockRelationForStockId($stockId);
    }
}
