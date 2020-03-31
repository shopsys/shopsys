<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

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
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \App\Model\Stock\StockRepository $stockRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        StockRepository $stockRepository
    ) {
        $this->stockRepository = $stockRepository;
        $this->em = $em;
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
    public function getStocksWithoutCentralByDomainIdIndexedByStockId(int $domainId): array
    {
        $stocks = $this->stockRepository->getStocksWithoutCentralByDomainId($domainId);
        $stocksById = [];
        foreach ($stocks as $stock) {
            $stocksById[$stock->getId()] = $stock;
        }

        return $stocksById;
    }
}
