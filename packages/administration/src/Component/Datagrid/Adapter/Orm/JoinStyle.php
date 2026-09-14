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

    public function join(QueryBuilder $queryBuilder, string $currentAlias, PathStep $step, string $joinAlias): void
    {
        match ($this) {
            self::LISTING => $queryBuilder->leftJoin($step->targetEntityClass, $joinAlias, Join::WITH, $this->createRangeJoinCondition($currentAlias, $step, $joinAlias)),
        };
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
