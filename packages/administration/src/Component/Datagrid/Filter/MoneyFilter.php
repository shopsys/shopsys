<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

/**
 * A filter over an amount of money. The input shows no currency symbol.
 *
 * The amount is compared regardless of the currency. Narrow a list with prices in several currencies to one
 * currency first, e.g. by a domain.
 */
class MoneyFilter extends AbstractFilter
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
        return MoneyType::class;
    }

    /**
     * Amounts are stored with six decimal places, the default scale of two would round the entered amount.
     *
     * {@inheritdoc}
     */
    #[Override]
    protected function getDefaultValueFormOptions(ExpressionValueArityEnum $arity): array
    {
        return [
            'scale' => 6,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function getApplicableValueTypes(): array
    {
        return [PathValueTypeEnum::MONEY];
    }
}
