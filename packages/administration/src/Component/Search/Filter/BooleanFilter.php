<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;

/**
 * Filter on a boolean field with no value input — the operator alone decides ("is" / "not").
 */
final class BooleanFilter extends AbstractFieldFilter
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
        return [Operator::IS, Operator::IS_NOT];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return HiddenType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        // the hidden value input submits a fixed value, so the (ignored) empty value does not skip the rule
        return ['empty_data' => '1'];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        $fieldExpression = $this->getSingleFieldExpression();

        foreach ($rules as $rule) {
            if ($rule->operator === Operator::IS) {
                $queryBuilder->andWhere(sprintf('%s = true', $fieldExpression));
            } else {
                // "not" must also match NULL fields (SQL NULL never satisfies !=)
                $queryBuilder->andWhere(sprintf('(%s != true OR %s IS NULL)', $fieldExpression, $fieldExpression));
            }
        }
    }
}
