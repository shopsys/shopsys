<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ToManySelectNotSupportedException;

/**
 * Owns the query of a datagrid and translates dot notation paths into its selects. The joins a path needs are added
 * by `PathJoiner`.
 */
final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    private QueryBuilder $queryBuilder;

    private PathJoiner $pathJoiner;

    /**
     * @var array<string, string>
     */
    private array $selects = [];

    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly EntityManagerInterface $entityManager,
        private readonly PathWalkerInterface $pathWalker,
        string $locale,
    ) {
        $this->queryBuilder = $this->createQueryBuilder(self::DEFAULT_ALIAS);
        $this->pathJoiner = new PathJoiner($this->queryBuilder, self::DEFAULT_ALIAS, $locale, JoinStyle::LISTING);
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
        $this->pathJoiner = $this->pathJoiner->withQueryBuilder($this->queryBuilder);
    }

    public function addSelect(string $select): void
    {
        if (array_key_exists($select, $this->selects)) {
            return;
        }

        $pathResolution = $this->resolvePath($select);

        if ($pathResolution->isToMany()) {
            throw new ToManySelectNotSupportedException($select);
        }

        $this->selects[$select] = $pathResolution->selectAlias;
        $this->queryBuilder->addSelect($pathResolution->dqlExpression . ' AS ' . $pathResolution->selectAlias);
    }

    /**
     * Resolves a dot notation path against the query, joining every association on the way.
     *
     * A path through a to-many association is never joined into the query, it would multiply the listed record —
     * such a path is returned without a DQL expression, so that each role can handle it on its own (a select is
     * not supported).
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function resolvePath(string $path): PathResolution
    {
        return $this->pathJoiner->resolve($this->pathWalker->walk($this->entityClass, $path));
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    private function createQueryBuilder(string $alias): QueryBuilder
    {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $this->entityManager->getRepository($this->entityClass);

        return $repository->createQueryBuilder($alias)->resetDQLPart('select');
    }
}
