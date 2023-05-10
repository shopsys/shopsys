<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\DuplicatedAliasException;
use Shopsys\FrameworkBundle\Component\Doctrine\Exception\InvalidCountOfAliasesException;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class QueryBuilderExtender
{
    protected const REQUIRED_ALIASES_COUNT = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver
     */
    protected $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(EntityNameResolver $entityNameResolver)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $class
     * @param string $alias
     * @param string $condition
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function addOrExtendJoin(QueryBuilder $queryBuilder, $class, $alias, $condition)
    {
        $joins = $this->getJoinsFromQueryBuilder($queryBuilder);

        $joinAlreadyUsed = false;
        $resolvedClass = $this->entityNameResolver->resolve($class);

        foreach ($joins as $join) {
            if ($join->getAlias() === $alias) {
                $resolvedJoinClass = $this->entityNameResolver->resolve($join->getJoin());

                if ($resolvedJoinClass !== $resolvedClass) {
                    throw new DuplicatedAliasException($alias, $resolvedClass, $resolvedJoinClass);
                }

                $joinAlreadyUsed = true;

                break;
            }
        }

        if (!$joinAlreadyUsed) {
            $queryBuilder->join(
                $resolvedClass,
                $alias,
                Join::WITH,
                $condition
            );
        } else {
            $queryBuilder->andWhere($condition);
        }

        return $queryBuilder;
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @return string
     */
    protected function getRootAlias(QueryBuilder $queryBuilder)
    {
        $rootAliases = $queryBuilder->getRootAliases();

        if (count($rootAliases) !== static::REQUIRED_ALIASES_COUNT) {
            throw new InvalidCountOfAliasesException($rootAliases);
        }

        return reset($rootAliases);
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
