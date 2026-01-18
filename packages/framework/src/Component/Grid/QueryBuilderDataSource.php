<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Grid;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\GroupedScalarHydrator;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Paginator\QueryPaginator;

class QueryBuilderDataSource implements DataSourceInterface
{
    protected ?int $totalCount = null;

    /**
     * @param array<string, mixed> $hints
     */
    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly string $rowIdSourceColumnName,
        protected array $hints,
    ) {
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

        $queryPaginator = new QueryPaginator(
            $queryBuilder,
            GroupedScalarHydrator::HYDRATION_MODE,
            $this->hints,
        );

        return $queryPaginator->getResult($page, $limit, $this->getTotalRowsCount());
    }

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function getOneRow(int|string $rowId): array
    {
        $queryBuilder = clone $this->queryBuilder;
        $this->prepareQueryWithOneRow($queryBuilder, $rowId);

        return $queryBuilder->getQuery()->getSingleResult(GroupedScalarHydrator::HYDRATION_MODE);
    }

    #[Override]
    public function getTotalRowsCount(): int
    {
        if ($this->totalCount === null) {
            $queryPaginator = new QueryPaginator(
                $this->queryBuilder,
                GroupedScalarHydrator::HYDRATION_MODE,
                $this->hints,
            );

            $this->totalCount = $queryPaginator->getTotalCount();
        }

        return $this->totalCount;
    }

    protected function addQueryOrder(
        QueryBuilder $queryBuilder,
        string $orderSourceColumnName,
        string $orderDirection,
    ): void {
        $queryBuilder->orderBy($orderSourceColumnName, $orderDirection);
    }

    protected function prepareQueryWithOneRow(QueryBuilder $queryBuilder, int|string $rowId): void
    {
        $queryBuilder
            ->andWhere($this->rowIdSourceColumnName . ' = :rowId')
            ->setParameter('rowId', $rowId)
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');
    }

    #[Override]
    public function getRowIdSourceColumnName(): string
    {
        return $this->rowIdSourceColumnName;
    }
}
