<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class QueryBuilderWithRowManipulatorDataSource extends QueryBuilderDataSource
{
    /**
     * @var callable
     */
    protected $manipulateRowCallback;

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     * @param \Closure $manipulateRowCallback
     */
    public function __construct(QueryBuilder $queryBuilder, string $rowIdSourceColumnName, Closure $manipulateRowCallback)
    {
        parent::__construct($queryBuilder, $rowIdSourceColumnName);

        $this->manipulateRowCallback = $manipulateRowCallback;
    }

    /**
     * @param int $rowId
     * @return mixed[]
     */
    public function getOneRow($rowId): array
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
        $limit = null,
        $page = 1,
        $orderSourceColumnName = null,
        $orderDirection = self::ORDER_ASC,
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
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
