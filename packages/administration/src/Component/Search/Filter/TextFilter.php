<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRule;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * Case- and accent-insensitive text filter with substring and exact matching.
 * A rule matches when any of the searched fields matches; negative operators require all fields to match.
 */
final class TextFilter extends AbstractFieldFilter
{
    public static function create(string $name, string $label): self
    {
        return new self($name, $label);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [Operator::CONTAINS, Operator::NOT_CONTAINS, Operator::IS, Operator::IS_NOT, Operator::NOT_SET];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return TextType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        foreach ($rules as $rule) {
            $queryBuilder->andWhere($this->getRuleCondition($queryBuilder, $rule));
        }
    }

    private function getRuleCondition(QueryBuilder $queryBuilder, FilterRule $rule): string
    {
        $conditions = [];

        foreach ($this->getFieldExpressions() as $expression) {
            // negative operators must also match NULL fields (SQL NULL never satisfies NOT LIKE / !=)
            $conditions[] = match ($rule->operator) {
                Operator::CONTAINS => sprintf('NORMALIZED(%s) LIKE NORMALIZED(:%s)', $expression, $rule->param()),
                Operator::NOT_CONTAINS => sprintf('(NORMALIZED(%s) NOT LIKE NORMALIZED(:%s) OR %s IS NULL)', $expression, $rule->param(), $expression),
                Operator::IS => sprintf('NORMALIZED(%s) = NORMALIZED(:%s)', $expression, $rule->param()),
                Operator::IS_NOT => sprintf('(NORMALIZED(%s) != NORMALIZED(:%s) OR %s IS NULL)', $expression, $rule->param(), $expression),
                Operator::NOT_SET => sprintf('%s IS NULL', $expression),
                default => throw new InvalidArgumentException(sprintf('Unsupported operator "%s".', $rule->operator->value)),
            };
        }

        if ($rule->operator->hasValue()) {
            $isSubstringMatch = in_array($rule->operator, [Operator::CONTAINS, Operator::NOT_CONTAINS], true);
            $queryBuilder->setParameter($rule->param(), $isSubstringMatch ? $rule->getLikeValue() : (string)$rule->value);
        }

        $isNegative = in_array($rule->operator, [Operator::NOT_CONTAINS, Operator::IS_NOT, Operator::NOT_SET], true);

        return '(' . implode($isNegative ? ' AND ' : ' OR ', $conditions) . ')';
    }
}
