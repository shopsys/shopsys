<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * @template T of array<string, mixed>
 * @implements \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T>
 */
class MoneyConvertingDataSourceDecorator implements DataSourceInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T>
     */
    protected $innerDataSource;

    /**
     * @var string[]
     */
    protected $moneyColumnNames;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T> $innerDataSource
     * @param string[] $moneyColumnNames
     */
    public function __construct(DataSourceInterface $innerDataSource, array $moneyColumnNames)
    {
        $this->innerDataSource = $innerDataSource;
        $this->moneyColumnNames = $moneyColumnNames;
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string|null $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        ?string $orderDirection = self::ORDER_ASC
    ): PaginationResult {
        $paginationResult = $this->innerDataSource->getPaginatedRows(
            $limit,
            $page,
            $orderSourceColumnName,
            $orderDirection
        );

        $results = $paginationResult->getResults();
        foreach ($results as $key => $result) {
            $results[$key] = $this->convertRow($result);
        }

        /** @var \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T> $convertedPaginationResult */
        $convertedPaginationResult = new PaginationResult(
            $paginationResult->getPage(),
            $paginationResult->getPageSize(),
            $paginationResult->getTotalCount(),
            $results
        );
        return $convertedPaginationResult;
    }

    /**
     * @param int $rowId
     * @return array<string, mixed>
     */
    public function getOneRow(int $rowId): array
    {
        $row = $this->innerDataSource->getOneRow($rowId);

        return $this->convertRow($row);
    }

    /**
     * @return int
     */
    public function getTotalRowsCount(): int
    {
        return $this->innerDataSource->getTotalRowsCount();
    }

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string
    {
        return $this->innerDataSource->getRowIdSourceColumnName();
    }

    /**
     * @param T $row
     * @return array<string, mixed>
     */
    protected function convertRow(array $row): array
    {
        foreach ($this->moneyColumnNames as $columnName) {
            $row[$columnName] = $row[$columnName] !== null ? Money::create($row[$columnName]) : null;
        }

        return $row;
    }
}
