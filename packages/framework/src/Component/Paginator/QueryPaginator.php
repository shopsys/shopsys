<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlParametersFlattener;

class QueryPaginator implements PaginatorInterface
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string|null $hydrationMode
     */
    public function __construct(
        protected readonly QueryBuilder $queryBuilder,
        protected readonly ?string $hydrationMode = null,
    ) {
    }

    /**
     * @param int $page
     * @param int|null $pageSize
     * @param int|null $totalCount
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
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
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, SortableNullsWalker::class);

        $results = $query->execute(null, $this->hydrationMode);

        return new PaginationResult($page, $pageSize, $totalCount, $results);
    }

    /**
     * @return int
     */
    public function getTotalCount(): int
    {
        $totalNativeQuery = $this->getTotalNativeQuery($this->queryBuilder);

        return $totalNativeQuery->getSingleScalarResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\NativeQuery
     */
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
