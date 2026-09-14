<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ToManySelectNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;

/**
 * Owns the query of a datagrid and translates dot notation paths into it — the joins and the selects
 * the paths need.
 */
final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    public const string TRANSLATIONS_ASSOCIATION = PathWalker::TRANSLATIONS_ASSOCIATION;

    private QueryBuilder $queryBuilder;

    private string $rootAlias;

    private PathWalker $pathWalker;

    /**
     * @var array<string, string>
     */
    private array $selects = [];

    /**
     * Aliases of the joins already added, keyed by themselves. The alias is derived from the resolved path,
     * so a join is never added twice and two paths never share an alias.
     *
     * @var array<string, string>
     */
    private array $joins = [];

    /**
     * @param class-string $entityClass
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $locale,
    ) {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $entityManager->getRepository($entityClass);

        $this->queryBuilder = $repository->createQueryBuilder(self::DEFAULT_ALIAS);
        $this->rootAlias = current($this->queryBuilder->getRootAliases());
        $this->queryBuilder->resetDQLPart('select');
        $this->pathWalker = new PathWalker($entityManager);
    }

    /**
     * Describes what a path leads to without touching the query — safe to call any time, any number of times.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function describePath(string $path): PathDescription
    {
        return $this->pathWalker->walk($this->entityClass, $path)->description;
    }

    public function __clone()
    {
        $this->queryBuilder = clone $this->queryBuilder;
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
     * A path through a to-many association is never joined into the main query — joining it would list
     * the entity once per related row — so such a path is returned without a DQL expression.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function resolvePath(string $path): PathResolution
    {
        $walkedPath = $this->pathWalker->walk($this->entityClass, $path);
        $selectAlias = $this->getAlias($path);

        if ($walkedPath->description->isToMany()) {
            return new PathResolution(null, $selectAlias, null, $walkedPath->description);
        }

        $alias = $this->rootAlias;
        $terminalStep = null;

        foreach ($walkedPath->steps as $step) {
            if ($step->kind === PathStepKind::TRANSLATIONS) {
                $alias = $this->joinTranslations($alias);
            } elseif ($step->kind === PathStepKind::ASSOCIATION) {
                $alias = $this->joinAssociation($step->partPath, $step->field, $alias);
            } else {
                $terminalStep = $step;
            }
        }

        $dqlExpression = match ($terminalStep?->kind) {
            PathStepKind::FIELD => $alias . '.' . $terminalStep->field,
            PathStepKind::TRANSLATED_FIELD => $this->joinTranslations($alias) . '.' . $terminalStep->field,
            PathStepKind::IDENTITY => sprintf('IDENTITY(%s.%s)', $alias, $terminalStep->field),
            // the path ends with an association, addressed by the alias of its join
            null => $alias,
            default => throw new LogicException(sprintf('Step "%s" cannot end a path.', $terminalStep->kind->name)),
        };

        return new PathResolution($dqlExpression, $selectAlias, $terminalStep?->fieldType, $walkedPath->description);
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    private function getAlias(string $part): string
    {
        return strtr($part, ['.' => '__']);
    }

    /**
     * @return string Alias of the joined association
     */
    private function joinAssociation(string $pathToJoin, string $fieldName, string $currentAlias): string
    {
        $joinAlias = $this->getAlias($pathToJoin) . '_join';

        if (array_key_exists($joinAlias, $this->joins)) {
            return $joinAlias;
        }

        $this->joins[$joinAlias] = $joinAlias;

        // the association is joined by its path, so that Doctrine derives the join condition from the
        // mapping — the identifier of the target entity may be named differently or be composed of
        // several columns
        $this->queryBuilder->leftJoin("{$currentAlias}.{$fieldName}", $joinAlias);

        return $joinAlias;
    }

    /**
     * @return string Alias of the joined translations of the given alias
     */
    private function joinTranslations(string $currentAlias): string
    {
        $joinAlias = $currentAlias . '_tr';

        if (array_key_exists($joinAlias, $this->joins)) {
            return $joinAlias;
        }

        $this->joins[$joinAlias] = $joinAlias;

        $this->queryBuilder->leftJoin(
            $currentAlias . '.' . self::TRANSLATIONS_ASSOCIATION,
            $joinAlias,
            Join::WITH,
            "{$joinAlias}.locale = :{$joinAlias}_locale",
        );
        $this->queryBuilder->setParameter("{$joinAlias}_locale", $this->locale);

        return $joinAlias;
    }
}
