<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Closure;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use LogicException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ToManySelectNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Stringable;

/**
 * Owns the query of a datagrid and translates dot notation paths into it — the joins, the selects, the
 * parameters and the correlated subqueries the paths need.
 */
final class ProxyQuery
{
    public const DEFAULT_ALIAS = 'o';

    public const string TRANSLATIONS_ASSOCIATION = PathWalker::TRANSLATIONS_ASSOCIATION;

    private const string SUBQUERY_ALIAS_PREFIX = 'dgsub_';

    private const string PARAMETER_NAME_PREFIX = 'dgexpr_';

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

    private int $parameterCounter = 0;

    private int $subqueryCounter = 0;

    /**
     * @param class-string $entityClass
     * @param bool $correlated Whether the query is a correlated subquery of another one, which is how a
     *     condition on a to-many path is matched. Such a query joins to-many associations — reaching
     *     through one is its whole purpose — and joins them inwardly, so that a record with no related
     *     row at all is never matched.
     */
    public function __construct(
        private readonly string $entityClass,
        private readonly EntityManagerInterface $entityManager,
        private readonly string $locale,
        string $alias = self::DEFAULT_ALIAS,
        private readonly bool $correlated = false,
    ) {
        /** @var \Doctrine\ORM\EntityRepository $repository */
        $repository = $entityManager->getRepository($entityClass);

        $this->queryBuilder = $repository->createQueryBuilder($alias);
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

    /**
     * Narrows the query by an expression built for it.
     */
    public function applyExpression(Stringable|string $expression): void
    {
        $this->queryBuilder->andWhere($expression);
    }

    /**
     * Registers a value of an expression under a name of its own.
     *
     * @param string|null $type Doctrine type the value is converted by, typically the type of the compared field
     * @return string Name of the parameter, to be used as `:name` in the expression
     */
    public function addParameter(mixed $value, ?string $type = null): string
    {
        $name = self::PARAMETER_NAME_PREFIX . $this->parameterCounter++;
        $this->queryBuilder->setParameter($name, $value, $type);

        return $name;
    }

    /**
     * Builds a predicate on the given path, preparing the query for it — the associations on the way are
     * joined, and a path leading through a to-many association is matched by a correlated subquery, so that
     * the listed entity is never multiplied by the number of its related rows.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string $buildPredicate
     */
    public function buildPredicate(string $path, Closure $buildPredicate): string
    {
        $pathResolution = $this->resolvePath($path);

        if ($pathResolution->isToMany() === false) {
            return $buildPredicate($pathResolution);
        }

        return $this->buildCorrelatedSubquery($path, $buildPredicate);
    }

    /**
     * Builds a predicate matching a record with at least one related row on the given to-many path.
     */
    public function buildExistsPredicate(string $path): string
    {
        return $this->buildCorrelatedSubquery($path, null);
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
     * A path through a to-many association is never joined into the main query — such a path is returned
     * without a DQL expression, so that each role can handle it on its own (a condition becomes a subquery,
     * a select is not supported).
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\PathNotFoundException
     */
    public function resolvePath(string $path): PathResolution
    {
        $walkedPath = $this->pathWalker->walk($this->entityClass, $path);
        $selectAlias = $this->getAlias($path);

        if ($walkedPath->description->isToMany() && $this->correlated === false) {
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
        $joinAlias = $this->getJoinAlias($pathToJoin);

        if (array_key_exists($joinAlias, $this->joins)) {
            return $joinAlias;
        }

        $this->joins[$joinAlias] = $joinAlias;

        // the association is joined by its path, so that Doctrine derives the join condition from the
        // mapping — the identifier of the target entity may be named differently or be composed of
        // several columns
        if ($this->correlated) {
            $this->queryBuilder->innerJoin("{$currentAlias}.{$fieldName}", $joinAlias);
        } else {
            $this->queryBuilder->leftJoin("{$currentAlias}.{$fieldName}", $joinAlias);
        }

        return $joinAlias;
    }

    /**
     * @return string Alias of the join of the given path, unique among the aliases of the query it belongs to
     */
    private function getJoinAlias(string $pathToJoin): string
    {
        $joinAlias = $this->getAlias($pathToJoin) . '_join';

        // a correlated subquery is nested in the query it correlates with, so its aliases must not
        // shadow the aliases of that query
        return $this->correlated ? $this->rootAlias . '_' . $joinAlias : $joinAlias;
    }

    /**
     * Matches a path leading through a to-many association by a correlated subquery over the same entity,
     * which reaches the related rows without multiplying the listed one.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string|null $buildPredicate Null matches any related row
     */
    private function buildCorrelatedSubquery(string $path, ?Closure $buildPredicate): string
    {
        $subqueryAlias = self::SUBQUERY_ALIAS_PREFIX . $this->subqueryCounter++;
        $subquery = new self($this->entityClass, $this->entityManager, $this->locale, $subqueryAlias, true);
        $pathResolution = $subquery->resolvePath($path);

        $identifierField = $this->entityManager->getClassMetadata($this->entityClass)->getSingleIdentifierFieldName();
        $subqueryBuilder = $subquery->getQueryBuilder()
            ->select('1')
            ->where(sprintf('%s.%s = %s.%s', $subqueryAlias, $identifierField, $this->rootAlias, $identifierField));

        if ($buildPredicate !== null) {
            $subqueryBuilder->andWhere($buildPredicate($pathResolution));
        }

        // the subquery is embedded as plain DQL, so the parameters of its own joins have to be carried
        // over to the query that is actually executed
        foreach ($subqueryBuilder->getParameters() as $parameter) {
            $this->queryBuilder->setParameter($parameter->getName(), $parameter->getValue(), $parameter->getType());
        }

        return sprintf('EXISTS(%s)', $subqueryBuilder->getDQL());
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
