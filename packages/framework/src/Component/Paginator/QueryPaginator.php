<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\Paginator;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;
use Shopsys\FrameworkBundle\Component\Doctrine\SqlParametersFlattener;

class QueryPaginator implements PaginatorInterface
{
    protected ?string $hydrationMode = null;

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string|null $hydrationMode
     */
    public function __construct(protected readonly QueryBuilder $queryBuilder, $hydrationMode = null)
    {
        $this->hydrationMode = $hydrationMode;
    }

    /**
     * @param int $page
     * @param int $pageSize
     * @return \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
     */
    public function getResult($page = 1, $pageSize = null)
    {
        $queryBuilder = clone $this->queryBuilder;

        if ($page < 1) {
            $page = 1;
        }

        $totalCount = $this->getTotalCount();

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
    public function getTotalCount()
    {
        $totalNativeQuery = $this->getTotalNativeQuery($this->queryBuilder);

        return $totalNativeQuery->getSingleScalarResult();
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\NativeQuery
     */
    protected function getTotalNativeQuery(QueryBuilder $queryBuilder)
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
