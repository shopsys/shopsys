<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StockFacade
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly StockRepository $stockRepository,
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly ProductStockFacade $productStockFacade,
        protected readonly StockFactory $stockFactory,
    ) {
    }

    public function create(StockData $stockData): Stock
    {
        $stock = $this->stockFactory->create($stockData);
        $this->em->persist($stock);
        $this->em->flush();

        $this->productStockFacade->createProductStockRelationForStockId($stock->getId());

        return $stock;
    }

    public function edit(int $stockId, StockData $stockData): Stock
    {
        $stock = $this->getById($stockId);

        $hasDomainsChanged = $stock->getEnabledIndexedByDomainId() !== $stockData->isEnabledByDomain;

        $stock->edit($stockData);
        $this->em->flush();

        $this->eventDispatcher->dispatch(new StockEvent($stock, $hasDomainsChanged), StockEvent::UPDATE);

        return $stock;
    }

    public function delete(int $stockId): void
    {
        $stock = $this->getById($stockId);

        $this->eventDispatcher->dispatch(new StockEvent($stock), StockEvent::DELETE);

        $this->em->remove($stock);
        $this->em->flush();
    }

    public function changeDefaultStock(Stock $stock): void
    {
        $this->stockRepository->changeDefaultStock($stock);
    }

    public function getById(int $stockId): Stock
    {
        return $this->stockRepository->getById($stockId);
    }

    public function getAllStockQueryBuilder(): QueryBuilder
    {
        return $this->stockRepository->getAllStocksQueryBuilder();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->stockRepository->getAllStocks();
    }

    public function findStockByExternalId(string $externalId): ?Stock
    {
        return $this->stockRepository->findStockByExternalId($externalId);
    }

    /**
     * @param int[] $stockIds
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
     */
    public function getStocksByIdsIndexedById(array $stockIds): array
    {
        return $this->stockRepository->getStocksByIdsIndexedById($stockIds);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Stock\Stock[]
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

    public function getCount(): int
    {
        return $this->stockRepository->getCount();
    }
}
