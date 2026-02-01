<?php

declare(strict_types=1);

namespace App\Component\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\FrameworkBundle\Component\Doctrine\QueryBuilderExtender as BaseQueryBuilderExtender;

class QueryBuilderExtender extends BaseQueryBuilderExtender
{
    #[Override]
    public function addOrExtendJoin(
        QueryBuilder $queryBuilder,
        string $class,
        string $alias,
        string $condition,
    ): QueryBuilder {
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
}
