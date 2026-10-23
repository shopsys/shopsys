<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * How a query joins the associations its paths lead through.
 *
 * @internal this class is not intended to be used directly by other developers
 */
enum JoinStyle
{
    /**
     * The query listing the records joins the entity an association leads to outwardly, as a range variable compared
     * with the association itself — a record without the association never disappears from the listing, Doctrine
     * selects a whole entity only through a range variable, and comparing with the association takes the identifier
     * from the mapping, however it is named. A path through a collection is never joined, it would multiply the record.
     */
    case LISTING;

    /**
     * A correlated subquery reaching the related rows of a collection joins by the association path — only that
     * reaches through a collection. A related row missing a to-one association has a missing value there, the way
     * a listed record does, while an empty collection has no related row at all, so a record without any is never
     * matched. A collection whose owner may be missing itself is joined outwardly and left out only when its owner
     * is there, so a missing owner is a missing value as well. The subquery is nested in the query listing
     * the records, so its aliases are prefixed not to shadow the aliases of that query.
     */
    case CORRELATED;

    public function joinsCollections(): bool
    {
        return $this === self::CORRELATED;
    }

    public function prefixesAliases(): bool
    {
        return $this === self::CORRELATED;
    }

    /**
     * @param bool $joinsFromRoot Whether the association belongs to the root entity of the query, which is never missing
     */
    public function join(
        QueryBuilder $queryBuilder,
        string $currentAlias,
        PathStep $step,
        string $joinAlias,
        bool $joinsFromRoot,
    ): void {
        match ($this) {
            self::LISTING => $queryBuilder->leftJoin($step->targetEntityClass, $joinAlias, Join::WITH, $this->createRangeJoinCondition($currentAlias, $step, $joinAlias)),
            self::CORRELATED => $this->joinCorrelated($queryBuilder, $currentAlias, $step, $joinAlias, $joinsFromRoot),
        };
    }

    protected function joinCorrelated(
        QueryBuilder $queryBuilder,
        string $currentAlias,
        PathStep $step,
        string $joinAlias,
        bool $joinsFromRoot,
    ): void {
        $association = sprintf('%s.%s', $currentAlias, $step->field);

        if ($step->isToMany === false) {
            $queryBuilder->leftJoin($association, $joinAlias);
        } elseif ($joinsFromRoot) {
            $queryBuilder->innerJoin($association, $joinAlias);
        } else {
            $queryBuilder
                ->leftJoin($association, $joinAlias)
                ->andWhere(sprintf('%s IS NOT NULL OR %s IS NULL', $joinAlias, $currentAlias));
        }
    }

    /**
     * DQL compares an association only on the side owning it, so the inverse side of a one-to-one is compared by
     * the association of the entity it leads to. An association to an entity identified by several fields cannot be
     * compared as a whole, so it is compared field by field.
     */
    protected function createRangeJoinCondition(string $currentAlias, PathStep $step, string $joinAlias): string
    {
        if ($step->mappedBy !== null) {
            return sprintf('%s.%s = %s', $joinAlias, $step->mappedBy, $currentAlias);
        }

        if ($step->targetIdentifierFields !== []) {
            return implode(' AND ', array_map(
                static fn (string $identifierField): string => sprintf("IDENTITY(%s.%s, '%s') = %s.%s", $currentAlias, $step->field, $identifierField, $joinAlias, $identifierField),
                $step->targetIdentifierFields,
            ));
        }

        return sprintf('%s.%s = %s', $currentAlias, $step->field, $joinAlias);
    }
}
