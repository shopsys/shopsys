<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Doctrine\DatagridHydrator;
use Shopsys\FrameworkBundle\Component\Grid\QueryBuilderWithRowManipulatorDataSource;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

final class DatagridDataSource extends QueryBuilderWithRowManipulatorDataSource
{
    public const string ORDER_PROPERTIES_SEPARATOR = ',';

    /**
     * Fields combining multiple properties pass them as a comma-separated list (see Datagrid::createView()),
     * the rows are then ordered by all of them in the given order
     */
    #[Override]
    protected function addQueryOrder(
        QueryBuilder $queryBuilder,
        string $orderSourceColumnName,
        string $orderDirection,
    ): void {
        $queryBuilder->resetDQLPart('orderBy');

        foreach (explode(self::ORDER_PROPERTIES_SEPARATOR, $orderSourceColumnName) as $orderProperty) {
            $queryBuilder->addOrderBy(
                str_replace('.', '__', $orderProperty),
                $this->resolveSortDirection($orderDirection),
            );
        }
    }

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
