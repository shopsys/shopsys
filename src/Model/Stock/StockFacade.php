<?php

declare(strict_types=1);


namespace App\Model\Stock;


use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

class StockFacade implements StockFacadeInterface
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
    )
    {
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
        return $this->stockRepository->findById($stockId);
    }

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStockQueryBuilderByDomain(int $domainId): QueryBuilder
    {
        return $this->stockRepository->getStockByDomainQueryBuilder($domainId);
    }

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllStockCountByDomainId(int $domainId): int
    {
        return $this->stockRepository->getAllStockCountByDomainId($domainId);
    }

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocksByDomainId(int $domainId): array
    {
        return $this->stockRepository->getAllStockByDomain($domainId);
    }

    /**
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocks(): array
    {
        return $this->stockRepository->getAllStocks();
    }


}