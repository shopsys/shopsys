<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;

/**
 * Shared batch semantics of the choice-like filters: all "is" rule values combine into one IN condition (OR),
 * all "not" rule values into one NOT IN condition.
 */
final class InClauseHelper
{
    public static function extendQueryBuilderWithGroupedValues(
        QueryBuilder $queryBuilder,
        string $fieldExpression,
        FilterRuleCollection $rules,
    ): void {
        $isValues = [];
        $isNotValues = [];
        $firstRule = null;

        foreach ($rules as $rule) {
            $firstRule ??= $rule;

            if ($rule->operator === Operator::IS) {
                $isValues[] = $rule->value;
            } else {
                $isNotValues[] = $rule->value;
            }
        }

        if ($firstRule === null) {
            return;
        }

        if ($isValues !== []) {
            $queryBuilder
                ->andWhere(sprintf('%s IN (:%s)', $fieldExpression, $firstRule->param('in')))
                ->setParameter($firstRule->param('in'), $isValues);
        }

        if ($isNotValues !== []) {
            // "not" must also match NULL fields (SQL NULL never satisfies NOT IN)
            $queryBuilder
                ->andWhere(sprintf('(%s NOT IN (:%s) OR %s IS NULL)', $fieldExpression, $firstRule->param('notIn'), $fieldExpression))
                ->setParameter($firstRule->param('notIn'), $isNotValues);
        }
    }
}
