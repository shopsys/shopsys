<?php

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

/**
 * @template T of array<string, mixed>
 * @implements \Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface<T>
 */
class QueryBuilderDataSource implements DataSourceInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @var string
     */
    protected $rowIdSourceColumnName;

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $rowIdSourceColumnName
     */
    public function __construct(QueryBuilder $queryBuilder, string $rowIdSourceColumnName)
    {
        $this->queryBuilder = $queryBuilder;
        $this->rowIdSourceColumnName = $rowIdSourceColumnName;
    }

    /**
     * @param int|null $limit
     * @param int $page
     * @param string|null $orderSourceColumnName
     * @param string|null $orderDirection
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult<T>
     */
    public function getPaginatedRows(
        ?int $limit = null,
        int $page = 1,
        ?string $orderSourceColumnName = null,
        ?string $orderDirection = self::ORDER_ASC
    ): PaginationResult {
        $queryBuilder = clone $this->queryBuilder;
        if ($orderSourceColumnName !== null) {
            $this->addQueryOrder($queryBuilder, $orderSourceColumnName, $orderDirection);
        }

        $queryPaginator = new QueryPaginator($queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);

        return $queryPaginator->getResult($page, $limit);
    }

    /**
     * @param int $rowId
     * @return T
     */
    public function getOneRow(int $rowId): array
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
        $queryPaginator = new QueryPaginator($this->queryBuilder, GroupedScalarHydrator::HYDRATION_MODE);
        return $queryPaginator->getTotalCount();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $orderSourceColumnName
     * @param string $orderDirection
     */
    protected function addQueryOrder(QueryBuilder $queryBuilder, string $orderSourceColumnName, string $orderDirection): void
    {
        $queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param int $rowId
     */
    protected function prepareQueryWithOneRow(QueryBuilder $queryBuilder, int $rowId): void
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
