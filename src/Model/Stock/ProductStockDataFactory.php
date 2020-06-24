<?php

declare(strict_types=1);

namespace App\Model\Stock;

class ProductStockDataFactory
{
    /**
     * @return \App\Model\Stock\ProductStockData
     */
    private function create()
    {
        return new ProductStockData();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromStock(Stock $stock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $stock->getName();
        $productStockData->stockId = $stock->getId();

        return $productStockData;
    }

    /**
     * @param \App\Model\Stock\ProductStock $productStock
     * @return \App\Model\Stock\ProductStockData
     */
    public function createFromProductStock(ProductStock $productStock): ProductStockData
    {
        $productStockData = $this->create();
        $productStockData->name = $productStock->getStock()->getName();
        $productStockData->stockId = $productStock->getStock()->getId();
        $productStockData->productQuantity = $productStock->getProductQuantity();
        $productStockData->productExposed = $productStock->isProductExposed();
        $productStockData->futureProductQuantity = $productStock->getFutureProductQuantity();
        $productStockData->dateOfStorage = $productStock->getDateOfStorage() !== null ? $productStock->getDateOfStorage()->format('j. M Y') : '';
        $productStockData->daysOfStorage = $this->calculateDaysOfStorage($productStock->getDateOfStorage());

        return $productStockData;
    }

    /**
     * @param \DateTime|null $dateOfStorage
     * @return int|null
     */
    private function calculateDaysOfStorage(?\DateTime $dateOfStorage): ?int
    {
        if ($dateOfStorage === null) {
            return null;
        }

        $nowDate = new \DateTime();
        $difference = $dateOfStorage->diff($nowDate)->days;

        if ($dateOfStorage < $nowDate) {
            $difference *= -1;
        }

        return $difference;
    }
}
