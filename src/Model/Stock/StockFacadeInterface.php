<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\QueryBuilder;

interface StockFacadeInterface
{
    /**
     * @param \App\Model\Stock\StockData $stockData
     * @return \App\Model\Stock\Stock
     */
    public function create(StockData $stockData): Stock;

    /**
     * @param int $stockId
     * @param \App\Model\Stock\StockData $stockData
     * @return \App\Model\Stock\Stock
     */
    public function edit(int $stockId, StockData $stockData): Stock;

    /**
     * @param int $stockId
     */
    public function delete(int $stockId): void;

    /**
     * @param int $stockId
     * @return \App\Model\Stock\Stock
     */
    public function getById(int $stockId): Stock;

    /**
     * @param int $domainId
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllStockQueryBuilderByDomain(int $domainId): QueryBuilder;

    /**
     * @param int $domainId
     * @return int
     */
    public function getAllStockCountByDomainId(int $domainId): int;

    /**
     * @param int $domainId
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocksByDomainId(int $domainId): array;

    /**
     * @return \App\Model\Stock\Stock[]
     */
    public function getAllStocks(): array;
}
