<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StockFacade
{
    /**
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Stock\StockRepository $stockRepository
     * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \App\Model\Stock\ProductStockFacade $productStockFacade
     */
    public function __construct(
        private EntityManagerInterface $em,
        private StockRepository $stockRepository,
        protected EventDispatcherInterface $eventDispatcher,
        private ProductStockFacade $productStockFacade,
    ) {
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

        $this->productStockFacade->createProductStockRelationForStockId($stock->getId());

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

        $hasDomainsChanged = $stock->getEnabledIndexedByDomainId() !== $stockData->isEnabledByDomain;

        $stock->edit($stockData);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new StockEvent($stock, $hasDomainsChanged), StockEvent::UPDATE);

        return $stock;
    }

    /**
     * @param int $stockId
     */
    public function delete(int $stockId): void
    {
        $stock = $this->getById($stockId);

        $this->eventDispatcher->dispatch(new StockEvent($stock), StockEvent::DELETE);

        $this->em->remove($stock);
        $this->em->flush();
    }

    /**
     * @param \App\Model\Stock\Stock $stock
     */
    public function changeDefaultStock(Stock $stock): void
    {
        $this->stockRepository->changeDefaultStock($stock);
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
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStockQueryBuilder(): QueryBuilder
    {
        return $this->stockRepository->getAllStocksQueryBuilder();
    }

    /**
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->stockRepository->getAllStocks();
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
     * @param int[] $stockIds
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksByIdsIndexedById(array $stockIds): array
    {
        return $this->stockRepository->getStocksByIdsIndexedById($stockIds);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getStocksEnabledOnDomainIndexedByStockId(int $domainId): array
    {
        $stocks = $this->stockRepository->getStocksEnabledOnDomain($domainId);
        $stocksById = [];

        foreach ($stocks as $stock) {
            $stocksById[$stock->getId()] = $stock;
        }

        return $stocksById;
    }
}
