<?php

namespace Shopsys\FrameworkBundle\Component\Doctrine;

use BadMethodCallException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class QueryBuilderExtender
{
    /** @access protected */
    const REQUIRED_ALIASES_COUNT = 1;

    /**
     * @var \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver|null
     */
    protected $entityNameResolver;

    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver|null $entityNameResolver
     */
    public function __construct(?EntityNameResolver $entityNameResolver = null)
    {
        $this->entityNameResolver = $entityNameResolver;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function setEntityNameResolver(EntityNameResolver $entityNameResolver): void
    {
        if ($this->entityNameResolver !== null && $this->entityNameResolver !== $entityNameResolver) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->entityNameResolver === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->entityNameResolver = $entityNameResolver;
        }
    }

    /**
     * @param \Doctrine\ORM\QueryBuilder $queryBuilder
     * @param string $class
     * @param string $alias
     * @param string $condition
     * @return \Doctrine\ORM\QueryBuilder $queryBuilder
     */
    public function addOrExtendJoin(QueryBuilder $queryBuilder, $class, $alias, $condition)
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
            throw new \Shopsys\FrameworkBundle\Component\Doctrine\Exception\InvalidCountOfAliasesException($rootAliases);
        }
        $firstAlias = reset($rootAliases);

        return $firstAlias;
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
