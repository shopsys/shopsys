<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class QueryBuilderWithRowManipulatorDataSource extends QueryBuilderDataSource
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param \Closure $manipulateRowCallback
     */
    public function __construct(
        QueryBuilder $queryBuilder,
        string $rowIdSourceColumnName,
        protected readonly Closure $manipulateRowCallback,
    ) {
        parent::__construct($queryBuilder, $rowIdSourceColumnName);
    }

    /**
     * @param int $rowId
     * @return array
     */
    public function getOneRow(int $rowId): array
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
        $results = array_map($this->manipulateRowCallback, $originalPaginationResult->getResults());

        return new PaginationResult(
            $originalPaginationResult->getPage(),
            $originalPaginationResult->getPageSize(),
            $originalPaginationResult->getTotalCount(),
            $results,
        );
    }
}
