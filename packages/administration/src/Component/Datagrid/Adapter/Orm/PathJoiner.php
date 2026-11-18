<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * Joins what a walked path needs into one query and returns the DQL expression of its value.
 *
 * Every join is added once, under an alias derived from the path — `currency.format` → `currency__format_join`.
 * The selects and the conditions of the query share it.
 *
 * The main query listing the records joins the associations this way:
 *
 * - A to-one association is a `LEFT JOIN` of its target entity (an arbitrary join), compared with the association:
 *   `LEFT JOIN App\Currency currency_join WITH o.currency = currency_join`
 * - `LEFT JOIN` keeps a record without the association in the listing.
 * - The arbitrary join lets the query select the joined entity as a whole. Doctrine refuses that for an entity
 *   joined through an association.
 * - Comparing with the association takes the identifier from the mapping, whatever its name.
 * - A to-many association is never joined, it would multiply the listed record. Its path is matched by an `EXISTS`
 *   subquery instead.
 *
 * The correlated `EXISTS` subquery matching the related rows of a to-many association joins them through
 * the associations:
 *
 * - A collection of the root entity is an `INNER JOIN`, so a record with an empty collection is never matched:
 *   `INNER JOIN dgsub_0.items dgsub_0_items_join`
 * - A to-one association is a `LEFT JOIN`, so a related row without it has a missing value, as in the main query:
 *   `LEFT JOIN dgsub_0_items_join.product dgsub_0_items__product_join`
 * - A collection of a joined association is a `LEFT JOIN` required only when that association exists, so
 *   a missing association is a missing value as well:
 *   `LEFT JOIN dgsub_0_category_join.tags dgsub_0_category__tags_join`
 *   `WHERE dgsub_0_category__tags_join IS NOT NULL OR dgsub_0_category_join IS NULL`
 * - The aliases are prefixed by the alias of the subquery, so they never shadow the aliases of the main query.
 *
 * @internal query building of the ORM adapter, may change without upgrade notes — resolve a path by `ProxyQuery::resolvePath()` or `ProxyQuery::exists()` instead
 */
final class PathJoiner
{
    /**
     * Aliases of the joins already added, keyed by themselves.
     *
     * @var array<string, string>
     */
    private array $joins = [];

    public function __construct(
        private QueryBuilder $queryBuilder,
        private readonly string $rootAlias,
        private readonly string $locale,
        private readonly bool $correlated,
    ) {
    }

    /**
     * The same joins for a copy of the query, which already holds them.
     */
    public function withQueryBuilder(QueryBuilder $queryBuilder): self
    {
        $pathJoiner = clone $this;
        $pathJoiner->queryBuilder = $queryBuilder;

        return $pathJoiner;
    }

    public function resolve(WalkedPath $walkedPath): PathResolution
    {
        $description = $walkedPath->description;
        $selectAlias = $this->getAlias($description->path);

        if ($description->isToMany() && $this->correlated === false) {
            return new PathResolution(null, $selectAlias, null, $description);
        }

        $alias = $this->rootAlias;
        $dqlExpression = null;

        foreach ($walkedPath->steps as $step) {
            match ($step->kind) {
                PathStepKind::TRANSLATIONS => $alias = $this->joinTranslations($alias),
                PathStepKind::ASSOCIATION => $alias = $this->joinAssociation($step, $alias),
                PathStepKind::FIELD => $dqlExpression = $alias . '.' . $step->field,
                PathStepKind::TRANSLATED_FIELD => $dqlExpression = $this->joinTranslations($alias) . '.' . $step->field,
                PathStepKind::IDENTITY => $dqlExpression = sprintf('IDENTITY(%s.%s)', $alias, $step->field),
            };
        }

        // the path ends with an association, addressed by the alias of its join
        $dqlExpression ??= $alias;

        return new PathResolution($dqlExpression, $selectAlias, $walkedPath->getFieldType(), $description);
    }

    private function getAlias(string $path): string
    {
        return strtr($path, ['.' => '__']);
    }

    /**
     * @return string Alias of the joined association
     */
    private function joinAssociation(PathStep $step, string $currentAlias): string
    {
        $joinAlias = $this->getAlias($step->partPath) . '_join';

        if ($this->correlated) {
            $joinAlias = $this->rootAlias . '_' . $joinAlias;
        }

        if (array_key_exists($joinAlias, $this->joins) === false) {
            $this->joins[$joinAlias] = $joinAlias;

            if ($this->correlated) {
                $this->joinCorrelated($currentAlias, $step, $joinAlias);
            } else {
                $this->queryBuilder->leftJoin($step->targetEntityClass, $joinAlias, Join::WITH, $this->createRangeJoinCondition($currentAlias, $step, $joinAlias));
            }
        }

        return $joinAlias;
    }

    private function joinCorrelated(string $currentAlias, PathStep $step, string $joinAlias): void
    {
        $association = sprintf('%s.%s', $currentAlias, $step->field);

        if ($step->isToMany === false) {
            $this->queryBuilder->leftJoin($association, $joinAlias);
        } elseif ($currentAlias === $this->rootAlias) {
            $this->queryBuilder->innerJoin($association, $joinAlias);
        } else {
            $this->queryBuilder
                ->leftJoin($association, $joinAlias)
                ->andWhere(sprintf('%s IS NOT NULL OR %s IS NULL', $joinAlias, $currentAlias));
        }
    }

    /**
     * DQL compares an association only on its owning side, so the inverse side of a one-to-one is compared by
     * the association of the joined entity — `detail_join.product = o`.
     */
    private function createRangeJoinCondition(string $currentAlias, PathStep $step, string $joinAlias): string
    {
        if ($step->mappedBy !== null) {
            return sprintf('%s.%s = %s', $joinAlias, $step->mappedBy, $currentAlias);
        }

        return sprintf('%s.%s = %s', $currentAlias, $step->field, $joinAlias);
    }

    /**
     * @return string Alias of the joined translations of the given alias
     */
    private function joinTranslations(string $currentAlias): string
    {
        $joinAlias = $currentAlias . '_tr';

        if (array_key_exists($joinAlias, $this->joins) === false) {
            $this->joins[$joinAlias] = $joinAlias;
            $this->queryBuilder->leftJoin(
                $currentAlias . '.' . PathWalker::TRANSLATIONS_ASSOCIATION,
                $joinAlias,
                Join::WITH,
                sprintf('%1$s.locale = :%1$s_locale', $joinAlias),
            );
            $this->queryBuilder->setParameter($joinAlias . '_locale', $this->locale);
        }

        return $joinAlias;
    }
}
