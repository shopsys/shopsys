<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;

class StockFacade
{
    /**
     * @var \App\Model\Stock\StockRepository
     */
    private $stockRepository;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \App\Model\Stock\ProductStockFacade
     */
    private $productStockFacade;

    /**
     * @var \App\Model\Stock\StockSettingsDataFactory
     */
    private $stockSettingsDataFactory;

    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Stock\StockRepository $stockRepository
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     * @param \App\Model\Stock\StockSettingsDataFactory $stockSettingsDataFactory
     */
    public function __construct(
        EntityManagerInterface $em,
        StockRepository $stockRepository,
        ProductStockFacade $productStockFacade,
        StockSettingsDataFactory $stockSettingsDataFactory
    ) {
        $this->stockRepository = $stockRepository;
        $this->em = $em;
        $this->productStockFacade = $productStockFacade;
        $this->stockSettingsDataFactory = $stockSettingsDataFactory;
    }

    /**
     * @param \App\Model\Stock\StockData $stockData
     * @return \App\Model\Stock\Stock
     */
    public function create(StockData $stockData): Stock
    {
        $stock = new Stock($stockData);
        $this->em->persist($stock);
        $this->em->flush();

        return $stock;
    }

    /**
     * @param int $stockId
     * @param \App\Model\Stock\StockData $stockData
     * @return \App\Model\Stock\Stock
     */
    public function edit(int $stockId, StockData $stockData): Stock
    {
        $stock = $this->getById($stockId);
        $stock->edit($stockData);
        $this->em->flush();

        return $stock;
    }

    /**
     * @param int $stockId
     */
    public function delete(int $stockId): void
    {
        $stock = $this->getById($stockId);
        $this->em->remove($stock);
        $this->em->flush();
    }

    /**
     * @param int $stockId
     * @return \App\Model\Stock\Stock
     */
    public function getById(int $stockId): Stock
    {
        return $this->stockRepository->getById($stockId);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStockQueryBuilderByDomain(int $domainId): QueryBuilder
    {
        return $this->stockRepository->getQueryBuilderByDomain($domainId);
    }

    /**
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->stockRepository->getAllStocks();
    }

    /**
     * @param string $name
     * @param int $domainId
     * @return \App\Model\Stock\Stock|null
     */
    public function findStockByNameAndDomainId(string $name, int $domainId): ?Stock
    {
        return $this->stockRepository->findStockByNameAndDomainId($name, $domainId);
    }

    /**
     * @param string $externalId
     * @return \App\Model\Stock\Stock|null
     */
    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->stockRepository->findStockByExternalId($externalId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksByDomainId(int $domainId): array
    {
        return $this->stockRepository->getStocksByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksWithoutCentralByDomainIdIndexedByStockId(int $domainId): array
    {
        $stocks = $this->stockRepository->getStocksWithoutCentralByDomainId($domainId);
        $stocksById = [];
        foreach ($stocks as $stock) {
            $stocksById[$stock->getId()] = $stock;
        }

        return $stocksById;
    }

    /**
     * @param int $domainId
     * @param \App\Model\Stock\Stock[] $stocks
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct[] $quantifiedProducts
     * @return int[]
     */
    public function getStockDayAvailabilitiesIndexedByStockId(int $domainId, array $stocks, array $quantifiedProducts)
    {
        $maximumDayAvailabilityByStockId = [];
        foreach ($stocks as $stock) {
            $maximumDayAvailabilityByStockId[$stock->getId()] = 0;
        }

        foreach ($quantifiedProducts as $quantifiedProduct) {
            $maximumDayAvailabilityByStockId = $this->getMaximumDayAvailabilityForProductIndexedByStockId(
                $quantifiedProduct,
                $maximumDayAvailabilityByStockId,
                $domainId
            );
        }

        return $maximumDayAvailabilityByStockId;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int[] $maximumDayAvailabilityByStockId
     * @param int $domainId
     * @return int[]
     */
    private function getMaximumDayAvailabilityForProductIndexedByStockId(
        QuantifiedProduct $quantifiedProduct,
        array $maximumDayAvailabilityByStockId,
        int $domainId
    ): array {
        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();
        $productStocksByDomainIdIndexedByStockId = $this->productStockFacade->getProductStocksByProductAndDomainIdIndexedByStockId(
            $product,
            $domainId
        );
        $quantityOnAllStocks = $this->sumProductStockQuantities($productStocksByDomainIdIndexedByStockId);

        foreach ($maximumDayAvailabilityByStockId as $stockId => $maximumDayAvailability) {
            $productStock = $productStocksByDomainIdIndexedByStockId[$stockId] ?? null;
            $quantityOnStock = $productStock ? $productStock->getProductQuantity() : 0;
            $productDayAvailability = $this->getDayAvailabilityForProductAndStock(
                $quantifiedProduct,
                $quantityOnStock,
                $quantityOnAllStocks,
                $domainId
            );

            $maximumDayAvailabilityByStockId[$stockId] = max(
                $maximumDayAvailability,
                $productDayAvailability
            );
        }

        return $maximumDayAvailabilityByStockId;
    }

    /**
     * @param \App\Model\Stock\ProductStock[] $productStocksByDomainIdIndexedByStockId
     * @return int
     */
    private function sumProductStockQuantities(array $productStocksByDomainIdIndexedByStockId): int
    {
        $totalProductStocksQuantity = 0;
        foreach ($productStocksByDomainIdIndexedByStockId as $productStock) {
            $totalProductStocksQuantity += $productStock->getProductQuantity();
        }

        return $totalProductStocksQuantity;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct $quantifiedProduct
     * @param int $quantityOnStock
     * @param int $quantityOnAllStocks
     * @param int $domainId
     * @return int
     */
    private function getDayAvailabilityForProductAndStock(
        QuantifiedProduct $quantifiedProduct,
        int $quantityOnStock,
        int $quantityOnAllStocks,
        int $domainId
    ): int {
        if ($quantityOnStock >= $quantifiedProduct->getQuantity()) {
            return 0;
        }

        $stockSettingsData = $this->stockSettingsDataFactory->getForDomainId($domainId);

        if ($quantityOnAllStocks >= $quantifiedProduct->getQuantity()) {
            return (int)$stockSettingsData->transfer;
        }

        /** @var \App\Model\Product\Product $product */
        $product = $quantifiedProduct->getProduct();

        return $product->getVendorDeliveryDate() + $stockSettingsData->delivery;
    }
}
