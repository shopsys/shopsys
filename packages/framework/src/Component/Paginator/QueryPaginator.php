<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlParametersFlattener;

class QueryPaginator implements PaginatorInterface
{
    protected bool $includeMetaColumns = false;

    /**
     * @param array<string, mixed> $hints
     */
    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly ?string $hydrationMode = null,
        protected readonly array $hints = [],
    ) {
    }

    #[Override]
    public function getResult(
        int $page = 1,
        ?int $pageSize = null,
        ?int $totalCount = null,
    ): PaginationResult {
        $queryBuilder = clone $this->queryBuilder;

        if ($page < 1) {
            $page = 1;
        }

        if ($totalCount === null) {
            $totalCount = $this->getTotalCount();
        }

        if ($pageSize !== null) {
            $maxPage = (int)ceil($totalCount / $pageSize);

            if ($maxPage < 1) {
                $maxPage = 1;
            }

            if ($page > $maxPage) {
                $page = $maxPage;
            }

            $queryBuilder
                ->setFirstResult($pageSize * ($page - 1))
                ->setMaxResults($pageSize);
        }

        $query = $queryBuilder->getQuery();

        foreach ($this->hints as $hintName => $hintValue) {
            $query->setHint($hintName, $hintValue);
        }

        if ($this->includeMetaColumns) {
            $query->setHint(Query::HINT_INCLUDE_META_COLUMNS, true);
        }

        $results = $query->execute(null, $this->hydrationMode);

        return new PaginationResult($page, $pageSize, $totalCount, $results);
    }

    /**
     * Enables inclusion of meta columns in query results
     */
    public function includeMetaColumns(): static
    {
        $this->includeMetaColumns = true;

        return $this;
    }

    #[Override]
    public function getTotalCount(): int
    {
        $totalNativeQuery = $this->getTotalNativeQuery($this->queryBuilder);

        return $totalNativeQuery->getSingleScalarResult();
    }

    protected function getTotalNativeQuery(QueryBuilder $queryBuilder): NativeQuery
    {
        $em = $queryBuilder->getEntityManager();

        $totalQueryBuilder = clone $queryBuilder;
        $totalQueryBuilder
            ->setFirstResult(null)
            ->setMaxResults(null)
            ->resetDQLPart('orderBy');

        $query = $totalQueryBuilder->getQuery();

        $parametersAssoc = [];

        foreach ($query->getParameters() as $parameter) {
            $parametersAssoc[$parameter->getName()] = $parameter->getValue();
        }

        $flattenedParameters = SqlParametersFlattener::flattenArrayParameters(
            $query->getDQL(),
            $parametersAssoc,
        );

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        $sql = 'SELECT COUNT(*)::INTEGER AS total_count FROM (' . $query->getSQL() . ') ORIGINAL_QUERY';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total_count', 'totalCount');

        return $em->createNativeQuery($sql, $rsm)
            ->setParameters($flattenedParameters);
    }
}
