<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Closure;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class QueryBuilderWithRowManipulatorDataSource extends QueryBuilderDataSource
{
    /**
     * @var callable
     */
    private $manipulateRowCallback;
    
    public function __construct(QueryBuilder $queryBuilder, string $rowIdSourceColumnName, Closure $manipulateRowCallback)
    {
        parent::__construct($queryBuilder, $rowIdSourceColumnName);
        $this->manipulateRowCallback = $manipulateRowCallback;
    }
    
    public function getOneRow(int $rowId): array
    {
        $row = parent::getOneRow($rowId);
        return call_user_func($this->manipulateRowCallback, $row);
    }

    public function getPaginatedRows(?int $limit = null, int $page = 1, ?string $orderSourceColumnName = null, string $orderDirection = self::ORDER_ASC): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
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
