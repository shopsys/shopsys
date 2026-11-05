<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Validator\Constraints\Range;

/**
 * A filter over a number — an integer or a decimal, decided by the medium when it knows the path. Money is
 * no plain number, it is compared by a filter entering money.
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
    public function getValueFormType(string $arity): string
    {
        return $this->pathDescription?->valueType === PathValueTypeEnum::INTEGER ? IntegerType::class : NumberType::class;
    }

    /**
     * An integer beyond the range of its column fails the query, so the value is held within the bounds the medium
     * tells, on top of whatever else the declaration constrains it by.
     *
     * {@inheritdoc}
     */
    #[Override]
    public function getValueFormOptions(string $arity): array
    {
        $options = parent::getValueFormOptions($arity);
        $pathDescription = $this->pathDescription;

        if ($pathDescription === null || ($pathDescription->minimumValue === null && $pathDescription->maximumValue === null)) {
            return $options;
        }

        $constraints = $options['constraints'] ?? [];
        $options['constraints'] = [
            ...(is_array($constraints) ? $constraints : [$constraints]),
            new Range(min: $pathDescription->minimumValue, max: $pathDescription->maximumValue),
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
