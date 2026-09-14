<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

/**
 * A filter over a number — an integer or a decimal, decided by the medium when it knows the path.
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
    public function getValueFormType(string $operator): string
    {
        return $this->pathDescription?->valueType === PathValueTypeEnum::INTEGER ? IntegerType::class : NumberType::class;
    }
}
