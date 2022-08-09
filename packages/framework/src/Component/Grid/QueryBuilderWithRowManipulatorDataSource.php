<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

/**
 * @template T of array<string, mixed>
 * @extends \Shopsys\FrameworkBundle\Component\Grid\QueryBuilderDataSource<T>
 */
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
    public function __construct(QueryBuilder $queryBuilder, $rowIdSourceColumnName, Closure $manipulateRowCallback)
    {
        parent::__construct($queryBuilder, $rowIdSourceColumnName);

        $this->manipulateRowCallback = $manipulateRowCallback;
    }

    /**
     * @param int $rowId
     * @return T
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
     * @param string|null $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(?int $limit = null, int $page = 1, ?string $orderSourceColumnName = null, ?string $orderDirection = self::ORDER_ASC): PaginationResult
    {
        $originalPaginationResult = parent::getPaginatedRows($limit, $page, $orderSourceColumnName, $orderDirection);
        $results = array_map($this->manipulateRowCallback, $originalPaginationResult->getResults());
        return new PaginationResult(
            $originalPaginationResult->getPage(),
            $originalPaginationResult->getPageSize(),
            $originalPaginationResult->getTotalCount(),
            $results
        );
    }
}
