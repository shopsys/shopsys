<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Range;

/**
 * A filter over a number. Money is filtered by `MoneyFilter`.
 */
class NumericFilter extends AbstractFilter
{
    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultOperators(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS,
            ExpressionOperatorEnum::NOT_EQUALS,
            ExpressionOperatorEnum::GREATER_THAN,
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL,
            ExpressionOperatorEnum::LESS_THAN,
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL,
            ExpressionOperatorEnum::BETWEEN,
            ExpressionOperatorEnum::IS_NULL,
            ExpressionOperatorEnum::IS_NOT_NULL,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormType(ExpressionValueArityEnum $arity): string
    {
        return $this->pathDescription?->valueType === PathValueTypeEnum::INTEGER ? IntegerType::class : NumberType::class;
    }

    /**
     * Adds a `Range` constraint for the integer range of the path to the declared constraints.
     * An integer outside the range of its column would fail the query.
     *
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(ExpressionValueArityEnum $arity): array
    {
        $options = parent::getValueFormOptions($arity);
        $integerRange = $this->pathDescription?->integerRange;

        if ($integerRange === null) {
            return $options;
        }

        $constraints = $options['constraints'] ?? [];
        $options['constraints'] = [
            ...(is_array($constraints) ? $constraints : [$constraints]),
            new Range(min: $integerRange->minimum, max: $integerRange->maximum),
        ];

        return $options;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getApplicableValueTypes(): array
    {
        return [PathValueTypeEnum::INTEGER, PathValueTypeEnum::DECIMAL];
    }
}
