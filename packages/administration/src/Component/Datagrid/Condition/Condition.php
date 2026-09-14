<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

use DateTimeInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Typed constructors of the condition tree, one per operation of the vocabulary — the same signatures
 * `ExpressionBuilderInterface` has, so that code narrowing a datagrid reads the same whether it builds data
 * or an expression:
 *
 *     Condition::andX(
 *         Condition::in('domainId', [1, 2]),
 *         Condition::orX(Condition::contains('name', 'hrnek'), Condition::contains('catnum', 'hrnek')),
 *     )
 *
 * A comparison whose operator is only known at runtime is built directly as `new Comparison(...)`.
 *
 * @phpstan-import-type ScalarValue from \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface
 * @phpstan-import-type ComparableValue from \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface
 */
final class Condition
{
    /**
     * @param ScalarValue $value
     */
    public static function equals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::EQUALS, $value);
    }

    /**
     * @param ScalarValue $value
     */
    public static function notEquals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::NOT_EQUALS, $value);
    }

    public static function contains(string $path, string $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::CONTAINS, $value);
    }

    public static function startsWith(string $path, string $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::STARTS_WITH, $value);
    }

    public static function endsWith(string $path, string $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::ENDS_WITH, $value);
    }

    /**
     * @param ComparableValue $value
     */
    public static function greaterThan(string $path, string|int|float|DateTimeInterface|Money $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::GREATER_THAN, $value);
    }

    /**
     * @param ComparableValue $value
     */
    public static function greaterThanOrEqual(
        string $path,
        string|int|float|DateTimeInterface|Money $value,
    ): Comparison {
        return new Comparison($path, ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, $value);
    }

    /**
     * @param ComparableValue $value
     */
    public static function lessThan(string $path, string|int|float|DateTimeInterface|Money $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::LESS_THAN, $value);
    }

    /**
     * @param ComparableValue $value
     */
    public static function lessThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, $value);
    }

    /**
     * @param ComparableValue $from
     * @param ComparableValue $to
     */
    public static function between(
        string $path,
        string|int|float|DateTimeInterface|Money $from,
        string|int|float|DateTimeInterface|Money $to,
    ): Comparison {
        return new Comparison($path, ExpressionOperatorEnum::BETWEEN, [$from, $to]);
    }

    /**
     * @param list<ScalarValue> $values An empty list matches nothing
     */
    public static function in(string $path, array $values): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::IN, $values);
    }

    /**
     * @param list<ScalarValue> $values An empty list matches everything
     */
    public static function notIn(string $path, array $values): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::NOT_IN, $values);
    }

    public static function isNull(string $path): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::IS_NULL);
    }

    public static function isNotNull(string $path): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::IS_NOT_NULL);
    }

    public static function isEmpty(string $path): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::IS_EMPTY);
    }

    public static function isNotEmpty(string $path): Comparison
    {
        return new Comparison($path, ExpressionOperatorEnum::IS_NOT_EMPTY);
    }

    public static function not(ConditionInterface $condition): Negation
    {
        return new Negation($condition);
    }

    public static function andX(ConditionInterface ...$conditions): Composite
    {
        return new Composite(LogicalOperatorEnum::AND, array_values($conditions));
    }

    public static function orX(ConditionInterface ...$conditions): Composite
    {
        return new Composite(LogicalOperatorEnum::OR, array_values($conditions));
    }
}
