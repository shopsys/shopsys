<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class QueryBuilderWithRowManipulatorDataSource extends QueryBuilderDataSource
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param \Closure $manipulateRowCallback
     * @param string|null $hint
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        protected readonly Closure $manipulateRowCallback,
        ?string $hint = SortableNullsWalker::class,
    ) {
        parent::__construct($queryBuilder, $rowIdSourceColumnName, $hint);
    }

    /**
     * @param int|string $rowId
     * @return array
     */
    public function getOneRow(int|string $rowId): array
    {
        $row = parent::getOneRow($rowId);

        return call_user_func($this->manipulateRowCallback, $row);
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
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
