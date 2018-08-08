<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

class QueryBuilderDataSource implements DataSourceInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string
     */
    private $rowIdSourceColumnName;

    public function __construct(QueryBuilder $queryBuilder, string $rowIdSourceColumnName)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rowIdSourceColumnName = $rowIdSourceColumnName;
    }

    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        string $orderDirection = self::ORDER_ASC
    ): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult {
        $queryBuilder = clone $this->queryBuilder;
        if ($orderSourceColumnName !== null) {
            $this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
        }

        $queryPaginator = new QueryPaginator($queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);

        $paginationResult = $queryPaginator->getResult($page, $limit);
        /* @var $paginationResult \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult */

        return $paginationResult;
    }

    public function getOneRow(int $rowId): array
    {
        $queryBuilder = clone $this->queryBuilder;
        $this->prepareQueryWithOneRow($queryBuilder, $rowId);

        return $queryBuilder->getQuery()->getSingleResult(GroupedScalarHydrator::HYDRATION_MODE);
    }

    public function getTotalRowsCount(): int
    {
        $queryPaginator = new QueryPaginator($this->queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);
        return $queryPaginator->getTotalCount();
    }

    private function addQueryOrder(QueryBuilder $queryBuilder, string $orderSourceColumnName, string $orderDirection): void
    {
        $queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
    }

    private function prepareQueryWithOneRow(QueryBuilder $queryBuilder, int $rowId): void
    {
        $queryBuilder
            ->andWhere($this->rowIdSourceColumnName . ' = :rowId')
            ->setParameter('rowId', $rowId)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');
    }

    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }
}
