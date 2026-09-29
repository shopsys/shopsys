<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\FrameworkBundle\Component\Enum\AbstractEnum;

/**
 * The operations an expression builder is able to express. Every case is named after the method of
 * {@see \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface} it stands for,
 * and {@see \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCompiler} is the one
 * place that translates a case into the call.
 *
 * The vocabulary is shared by every medium, and a medium unable to evaluate an operation says so through
 * {@see \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionCapabilitiesInterface}. A project
 * extends the vocabulary by a subclass of this enum, a compiler decorating the default one, and a builder
 * expressing the added operations — never by changing the shared interface.
 */
class ExpressionOperatorEnum extends AbstractEnum
{
    public const string EQUALS = 'equals';
    public const string NOT_EQUALS = 'notEquals';
    public const string CONTAINS = 'contains';
    public const string STARTS_WITH = 'startsWith';
    public const string ENDS_WITH = 'endsWith';
    public const string GREATER_THAN = 'greaterThan';
    public const string GREATER_THAN_OR_EQUAL = 'greaterThanOrEqual';
    public const string LESS_THAN = 'lessThan';
    public const string LESS_THAN_OR_EQUAL = 'lessThanOrEqual';
    public const string BETWEEN = 'between';
    public const string IN = 'in';
    public const string NOT_IN = 'notIn';
    public const string IS_NULL = 'isNull';
    public const string IS_NOT_NULL = 'isNotNull';
    public const string IS_EMPTY = 'isEmpty';
    public const string IS_NOT_EMPTY = 'isNotEmpty';

    /**
     * @return string One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionValueArityEnum
     */
    public function getValueArity(string $operator): string
    {
        $this->validateCase($operator);

        return match ($operator) {
            self::IS_NULL, self::IS_NOT_NULL, self::IS_EMPTY, self::IS_NOT_EMPTY => ExpressionValueArityEnum::NONE,
            self::IN, self::NOT_IN => ExpressionValueArityEnum::LIST,
            self::BETWEEN => ExpressionValueArityEnum::RANGE,
            default => ExpressionValueArityEnum::SINGLE,
        };
    }
}
