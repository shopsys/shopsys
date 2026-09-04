<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

enum Operator: string
{
    case CONTAINS = 'contains';
    case NOT_CONTAINS = 'notContains';
    case IS = 'is';
    case IS_NOT = 'isNot';
    case NOT_SET = 'notSet';
    case GT = 'gt';
    case GTE = 'gte';
    case LT = 'lt';
    case LTE = 'lte';
    case BEFORE = 'before';
    case AFTER = 'after';
    case EXISTS = 'exists';
    case DOES_NOT_EXIST = 'doesNotExist';

    public function getLabel(): string
    {
        return match ($this) {
            self::CONTAINS => t('include'),
            self::NOT_CONTAINS => t('doesn\'t include'),
            self::IS => t('is'),
            self::IS_NOT => t('not'),
            self::NOT_SET => t('not entered'),
            self::GT => t('higher than'),
            self::GTE => t('higher or equal'),
            self::LT => t('lower than'),
            self::LTE => t('lower or equal'),
            self::BEFORE => t('before'),
            self::AFTER => t('after'),
            self::EXISTS => t('exists'),
            self::DOES_NOT_EXIST => t('does not exist'),
        };
    }

    /**
     * Whether the operator compares against a value entered by the administrator.
     */
    public function hasValue(): bool
    {
        return match ($this) {
            self::NOT_SET, self::EXISTS, self::DOES_NOT_EXIST => false,
            default => true,
        };
    }

    /**
     * @return string[] Values of the operators whose rules have no value input
     */
    public static function getValuelessOperatorValues(): array
    {
        $values = [];

        foreach (self::cases() as $operator) {
            if ($operator->hasValue() === false) {
                $values[] = $operator->value;
            }
        }

        return $values;
    }
}
