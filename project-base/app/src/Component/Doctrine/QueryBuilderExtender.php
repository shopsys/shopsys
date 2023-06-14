<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender as BaseQueryBuilderExtender;

class QueryBuilderExtender extends BaseQueryBuilderExtender
{
    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $class
     * @param string $alias
     * @param string $condition
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addOrExtendJoin(QueryBuilder $queryBuilder, $class, $alias, $condition): QueryBuilder
    {
        $joins = $this->getJoinsFromQueryBuilder($queryBuilder);

        $joinAlreadyUsed = false;

        $resolvedClass = $this->entityNameResolver->resolve($class);

        foreach ($joins as $join) {
            $resolvedJoinClass = $this->entityNameResolver->resolve($join->getJoin());

            if ($resolvedJoinClass === $resolvedClass) {
                $joinAlreadyUsed = true;

                break;
            }
        }

        if (!$joinAlreadyUsed) {
            $queryBuilder->join(
                $resolvedClass,
                $alias,
                Join::WITH,
                $condition,
            );
        } else {
            $queryBuilder->andWhere($condition);
        }

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return \Doctrine\ORM\Query\Expr\Join[]
     */
    protected function getJoinsFromQueryBuilder(QueryBuilder $queryBuilder): array
    {
        $rootAlias = $this->getRootAlias($queryBuilder);

        $joinDqlPart = $queryBuilder->getDQLPart('join');

        if (array_key_exists($rootAlias, $joinDqlPart) === true) {
            return $joinDqlPart[$rootAlias];
        }

        return [];
    }
}
