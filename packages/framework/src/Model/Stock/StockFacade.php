<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Model\Stock\Exception\DefaultStockNotEnabledException;
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
        $this->validateDefaultRequiresEnabled($stockData);

        $stock = $this->stockFactory->create($stockData);

        $this->em->persist($stock);

        $this->ensureSingleDefaultPerDomain($stock, $stockData);

        $this->em->flush();

        $this->productStockFacade->createProductStockRelationForStockId($stock->getId());

        $this->eventDispatcher->dispatch(new StockEvent($stock), StockEvent::CREATE);

        return $stock;
    }

    public function edit(int $stockId, StockData $stockData): Stock
    {
        $stock = $this->getById($stockId);

        $hasDomainsChanged = $stock->getEnabledIndexedByDomainId() !== $stockData->isEnabledByDomain;

        $this->validateDefaultRequiresEnabled($stockData);

        $stock->edit($stockData);

        $this->ensureSingleDefaultPerDomain($stock, $stockData);

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

    public function validateDefaultRequiresEnabled(StockData $stockData): void
    {
        foreach ($stockData->isDefaultByDomain as $domainId => $isDefault) {
            if ($isDefault && !($stockData->isEnabledByDomain[$domainId] ?? false)) {
                throw new DefaultStockNotEnabledException($domainId);
            }
        }
    }

    protected function ensureSingleDefaultPerDomain(Stock $stock, StockData $stockData): void
    {
        foreach ($stockData->isDefaultByDomain as $domainId => $isDefault) {
            if ($isDefault) {
                $otherDefaults = $this->stockRepository->getDefaultStockDomainsForDomainExcept($stock->getId(), $domainId);

                foreach ($otherDefaults as $stockDomain) {
                    $stockDomain->setDefault(false);
                }
            }
        }
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

    /**
     * @return array<int, int[]> stock ID => array of domain IDs where stock is default
     */
    public function getDefaultDomainIdsIndexedByStockId(): array
    {
        return $this->stockRepository->getDefaultDomainIdsIndexedByStockId();
    }

    /**
     * @param int[] $allDomainIds
     * @return int[] domain IDs that have no default stock
     */
    public function getDomainIdsWithoutDefaultStock(array $allDomainIds): array
    {
        return $this->stockRepository->getDomainIdsWithoutDefaultStock($allDomainIds);
    }

    /**
     * @return array{stockId: int, stockName: string, domainId: int}[]
     */
    public function getDefaultButDisabledStockDomains(): array
    {
        return $this->stockRepository->getDefaultButDisabledStockDomains();
    }
}
