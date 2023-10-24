<?php

declare(strict_types=1);

namespace App\Model\Stock;

use App\Model\Product\Product;
use Doctrine\ORM\EntityManagerInterface;

class ProductStockFacade
{
    private ProductStockRepository $productStockRepository;

    /**
     * @param \App\Model\Stock\ProductStockRepository $stockProductRepository
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function __construct(
        ProductStockRepository $stockProductRepository,
        private EntityManagerInterface $em,
    ) {
        $this->productStockRepository = $stockProductRepository;
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProduct(Product $product): array
    {
        return $this->productStockRepository->getProductStocksByProduct($product);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Stock\ProductStock[]
     */
    public function getProductStocksByProductAndDomainId(Product $product, int $domainId): array
    {
        return $this->productStockRepository->getProductStocksByProductAndDomainId($product, $domainId);
    }

    /**
     * @param \App\Model\Product\Product $product
     * @return \App\Model\Stock\ProductStock[]
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
     * @param \App\Model\Product\Product $product
     * @param int $domainId
     * @return \App\Model\Stock\ProductStock[]
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
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock[] $stocksIndexedById
     * @param \App\Model\Stock\ProductStockData[] $productStockDataItems
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
     * @param \App\Model\Product\Product $product
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\ProductStock
     */
    private function createProductStock(Product $product, Stock $stock): ProductStock
    {
        $productStock = new ProductStock($stock, $product);
        $this->em->persist($productStock);

        return $productStock;
    }

    /**
     * @param \App\Model\Product\Product $product
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
