<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Override;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class MoneyConvertingDataSourceDecorator implements DataSourceInterface
{
    /**
     * @param string[] $moneyColumnNames
     */
    public function __construct(
        protected readonly DataSourceInterface $innerDataSource,
        protected readonly array $moneyColumnNames,
    ) {
    }

    #[Override]
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        $paginationResult = $this->innerDataSource->getPaginatedRows(
            $limit,
            $page,
            $orderSourceColumnName,
            $orderDirection,
        );

        $results = $paginationResult->getResults();

        foreach ($results as $key => $result) {
            $results[$key] = $this->convertRow($result);
        }

        return new PaginationResult(
            $paginationResult->getPage(),
            $paginationResult->getPageSize(),
            $paginationResult->getTotalCount(),
            $results,
        );
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function getOneRow(int|string $rowId): array
    {
        $row = $this->innerDataSource->getOneRow($rowId);

        return $this->convertRow($row);
    }

    #[Override]
    public function getTotalRowsCount(): int
    {
        return $this->innerDataSource->getTotalRowsCount();
    }

    #[Override]
    public function getRowIdSourceColumnName(): string
    {
        return $this->innerDataSource->getRowIdSourceColumnName();
    }

    /**
     * @param array<string, mixed> $row
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
