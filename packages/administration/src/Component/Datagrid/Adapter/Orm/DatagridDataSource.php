<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Doctrine\DatagridHydrator;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult as PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

final class DatagridDataSource extends QueryBuilderWithRowManipulatorDataSource
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $orderSourceColumnName
     * @param string $orderDirection
     */
    #[Override]
    protected function addQueryOrder(
        QueryBuilder $queryBuilder,
        string $orderSourceColumnName,
        string $orderDirection,
    ): void {
        $queryBuilder->orderBy(str_replace('.', '__', $orderSourceColumnName), $orderDirection);
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    #[Override]
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC,
    ): PaginationResult {
        $queryBuilder = clone $this->queryBuilder;

        if ($orderSourceColumnName !== null) {
            $this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
        }

        $queryPaginator = new QueryPaginator($queryBuilder, DatagridHydrator::HYDRATION_MODE, $this->hints);
        $queryPaginator->includeMetaColumns();

        $originalPaginationResult = $queryPaginator->getResult($page, $limit, $this->getTotalRowsCount());
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
