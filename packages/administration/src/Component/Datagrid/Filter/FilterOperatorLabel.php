<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;

/**
 * How the operations of the vocabulary read in the filter form. A filter with a better word for its
 * kind of value (a date is "before" rather than "less than") overrides `getOperatorLabel()`.
 */
final class FilterOperatorLabel
{
    public static function get(string $operator): string
    {
        return self::getLabels()[$operator] ?? $operator;
    }

    /**
     * @return array<string, string>
     */
    private static function getLabels(): array
    {
        return [
            ExpressionOperatorEnum::EQUALS => t('is'),
            ExpressionOperatorEnum::NOT_EQUALS => t('is not'),
            ExpressionOperatorEnum::CONTAINS => t('contains'),
            ExpressionOperatorEnum::STARTS_WITH => t('starts with'),
            ExpressionOperatorEnum::ENDS_WITH => t('ends with'),
            ExpressionOperatorEnum::GREATER_THAN => t('is greater than'),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => t('is greater or equal to'),
            ExpressionOperatorEnum::LESS_THAN => t('is less than'),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => t('is less or equal to'),
            ExpressionOperatorEnum::BETWEEN => t('is between'),
            ExpressionOperatorEnum::IN => t('is one of'),
            ExpressionOperatorEnum::NOT_IN => t('is none of'),
            ExpressionOperatorEnum::IS_NULL => t('is not filled'),
            ExpressionOperatorEnum::IS_NOT_NULL => t('is filled'),
            ExpressionOperatorEnum::IS_EMPTY => t('has none'),
            ExpressionOperatorEnum::IS_NOT_EMPTY => t('has some'),
        ];
    }
}
