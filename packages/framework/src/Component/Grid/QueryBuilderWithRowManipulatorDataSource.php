<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class QueryBuilderWithRowManipulatorDataSource extends QueryBuilderDataSource
{
    /**
     * @param array<string, mixed> $hints
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        protected readonly Closure $manipulateRowCallback,
        protected array $hints,
    ) {
        parent::__construct($queryBuilder, $rowIdSourceColumnName, $hints);
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function getOneRow(int|string $rowId): array
    {
        $row = parent::getOneRow($rowId);

        return call_user_func($this->manipulateRowCallback, $row);
    }

    #[Override]
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        $originalPaginationResult = parent::getPaginatedRows($limit, $page, $orderSourceColumnName, $orderDirection);
        $results = $originalPaginationResult->getResults();

        foreach ($results as $key => $result) {
            $results[$key] = call_user_func($this->manipulateRowCallback, $result, $results);
        }

        return new PaginationResult(
            $originalPaginationResult->getPage(),
            $originalPaginationResult->getPageSize(),
            $originalPaginationResult->getTotalCount(),
            $results,
        );
    }
}
