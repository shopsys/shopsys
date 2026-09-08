<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search\Filter;

use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Override;
use Shopsys\AdministrationBundle\Component\Search\FilterRuleCollection;
use Shopsys\AdministrationBundle\Component\Search\Operator;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * Numeric comparison filter on a single field.
 */
class NumberFilter extends AbstractFieldFilter
{
    /**
     * @var array<string, mixed>
     */
    protected array $valueFormOptions = [];

    public static function create(string $name, string $label): static
    {
        return new static($name, $label);
    }

    /**
     * @param int $scale Number of allowed decimal places of the value input
     */
    public function withScale(int $scale): static
    {
        $this->valueFormOptions['scale'] = $scale;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getAllowedOperators(): array
    {
        return [Operator::IS, Operator::IS_NOT, Operator::GT, Operator::GTE, Operator::LT, Operator::LTE];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(): string
    {
        return NumberType::class;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(): array
    {
        return $this->valueFormOptions;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function extendQueryBuilder(QueryBuilder $queryBuilder, FilterRuleCollection $rules): void
    {
        $fieldExpression = $this->getSingleFieldExpression();

        foreach ($rules as $rule) {
            $dqlOperator = match ($rule->operator) {
                Operator::IS => '=',
                Operator::IS_NOT => '!=',
                Operator::GT => '>',
                Operator::GTE => '>=',
                Operator::LT => '<',
                Operator::LTE => '<=',
                default => throw new InvalidArgumentException(sprintf('Unsupported operator "%s".', $rule->operator->value)),
            };

            $condition = sprintf('%s %s :%s', $fieldExpression, $dqlOperator, $rule->param());

            if ($rule->operator === Operator::IS_NOT) {
                // "is not" must also match NULL fields (SQL NULL never satisfies !=)
                $condition = sprintf('(%s OR %s IS NULL)', $condition, $fieldExpression);
            }

            $queryBuilder
                ->andWhere($condition)
                ->setParameter($rule->param(), $rule->value);
        }
    }
}
