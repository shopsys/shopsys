<?php

namespace Shopsys\FrameworkBundle\Component\Paginator;

use Doctrine\DBAL\SQLParserUtils;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\SortableNullsWalker;

class QueryPaginator implements PaginatorInterface
{
    /**
     * @var \Doctrine\ORM\QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var string|null
     */
    private $hydrationMode;

    /**
     * @param string|null $hydrationMode
     */
    public function __construct(QueryBuilder $queryBuilder, ?string $hydrationMode = null)
    {
        $this->queryBuilder = $queryBuilder;
        $this->hydrationMode = $hydrationMode;
    }
    
    public function getResult(int $page = 1, int $pageSize = null): \Shopsys\FrameworkBundle\Component\Paginator\PaginationResult
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

    public function getTotalCount(): int
    {
        $totalNativeQuery = $this->getTotalNativeQuery($this->queryBuilder);

        return $totalNativeQuery->getSingleScalarResult();
    }

    private function getTotalNativeQuery(QueryBuilder $queryBuilder): \Doctrine\ORM\NativeQuery
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

        list(, $flatenedParameters) = SQLParserUtils::expandListParameters(
            $query->getDQL(),
            $parametersAssoc,
            []
        );

        // COUNT() returns BIGINT which is hydrated into string on 32-bit architecture
        $sql = 'SELECT COUNT(*)::INTEGER AS total_count FROM (' . $query->getSQL() . ') ORIGINAL_QUERY';

        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('total_count', 'totalCount');
        return $em->createNativeQuery($sql, $rsm)
            ->setParameters($flatenedParameters);
    }
}
