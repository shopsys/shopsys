<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult as PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

class QueryBuilderDataSource implements DataSourceInterface
{
    protected ?int $totalCount = null;

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     */
    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly string $rowIdSourceColumnName,
    ) {
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
        $queryBuilder = clone $this->queryBuilder;

        if ($orderSourceColumnName !== null) {
            $this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
        }

        $queryPaginator = new QueryPaginator($queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);

        return $queryPaginator->getResult($page, $limit, $this->getTotalRowsCount());
    }

    /**
     * @param int|string $rowId
     * @return array
     */
    public function getOneRow(int|string $rowId): array
    {
        $queryBuilder = clone $this->queryBuilder;
        $this->prepareQueryWithOneRow($queryBuilder, $rowId);

        return $queryBuilder->getQuery()->getSingleResult(GroupedScalarHydrator::HYDRATION_MODE);
    }

    /**
     * @return int
     */
    public function getTotalRowsCount(): int
    {
        if ($this->totalCount === null) {
            $queryPaginator = new QueryPaginator(
                $this->queryBuilder,
                GroupedScalarHydrator::HYDRATION_MODE,
            );

            $this->totalCount = $queryPaginator->getTotalCount();
        }

        return $this->totalCount;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $orderSourceColumnName
     * @param string $orderDirection
     */
    protected function addQueryOrder(
        QueryBuilder $queryBuilder,
        string $orderSourceColumnName,
        string $orderDirection,
    ): void {
        $queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int|string $rowId
     */
    protected function prepareQueryWithOneRow(QueryBuilder $queryBuilder, int|string $rowId): void
    {
        $queryBuilder
            ->andWhere($this->rowIdSourceColumnName . ' = :rowId')
            ->setParameter('rowId', $rowId)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');
    }

    /**
     * @return string
     */
    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }
}
