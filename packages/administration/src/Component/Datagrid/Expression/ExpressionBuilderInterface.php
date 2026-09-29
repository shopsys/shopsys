<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use DateTimeInterface;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Builds the expressions narrowing a datagrid in one medium — DQL for a Doctrine query, a callback for data
 * already in memory, a query clause for a search engine.
 *
 * The builder is bound to the data set it builds for and may prepare it for the expression, which is what
 * makes a path leading through an association work: `customerUser.email` adds the join it needs. The returned
 * expression is composable through `andX()`, `orX()` and `not()` and is only ever handed back to the adapter
 * that provided the builder.
 *
 * The concrete expression type is up to the medium (an implementation narrows the `object` return type to its
 * own), so an implementation has to reject an expression built by another medium.
 *
 * The vocabulary of operations is closed on purpose — it is what every medium has to understand. What a medium
 * cannot say through it, it says through a condition of its own (see `MediumSpecificConditionInterface`
 * and `DqlCondition`), and a project adding an operation does so by an own builder behind the factory of
 * the medium, never by changing this interface.
 *
 * @phpstan-type ScalarValue string|int|float|bool|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money
 * @phpstan-type ComparableValue string|int|float|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money
 * @template TExpression of object
 */
interface ExpressionBuilderInterface extends ExpressionCapabilitiesInterface
{
    /**
     * @param string $path Dot notation path, the same one datagrid fields use — `email`, `customerUser.email`
     * @param ScalarValue $value
     * @return TExpression
     */
    public function equals(string $path, string|int|float|bool|DateTimeInterface|Money $value): object;

    /**
     * @param ScalarValue $value
     * @return TExpression
     */
    public function notEquals(string $path, string|int|float|bool|DateTimeInterface|Money $value): object;

    /**
     * Matches a text containing the given one, ignoring case. Escaping the wildcards of the medium is the
     * implementation's job, so that a value the administrator typed is always taken literally.
     *
     * Applicable to a text path only — a medium knowing the type of the path refuses any other.
     *
     * @return TExpression
     */
    public function contains(string $path, string $value): object;

    /**
     * @return TExpression
     */
    public function startsWith(string $path, string $value): object;

    /**
     * @return TExpression
     */
    public function endsWith(string $path, string $value): object;

    /**
     * @param ComparableValue $value
     * @return TExpression
     */
    public function greaterThan(string $path, string|int|float|DateTimeInterface|Money $value): object;

    /**
     * @param ComparableValue $value
     * @return TExpression
     */
    public function greaterThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): object;

    /**
     * @param ComparableValue $value
     * @return TExpression
     */
    public function lessThan(string $path, string|int|float|DateTimeInterface|Money $value): object;

    /**
     * @param ComparableValue $value
     * @return TExpression
     */
    public function lessThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): object;

    /**
     * Matches a value within the given bounds, both of them included.
     *
     * @param ComparableValue $from
     * @param ComparableValue $to
     * @return TExpression
     */
    public function between(
        string $path,
        string|int|float|DateTimeInterface|Money $from,
        string|int|float|DateTimeInterface|Money $to,
    ): object;

    /**
     * @param list<ScalarValue> $values An empty list matches nothing
     * @return TExpression
     */
    public function in(string $path, array $values): object;

    /**
     * @param list<ScalarValue> $values An empty list matches everything
     * @return TExpression
     */
    public function notIn(string $path, array $values): object;

    /**
     * Matches a record whose single value is missing. Over a path leading through a to-many association it is
     * refused — a collection has no null to test, use `isEmpty()` instead.
     *
     * @return TExpression
     */
    public function isNull(string $path): object;

    /**
     * @return TExpression
     */
    public function isNotNull(string $path): object;

    /**
     * Matches a record with no related row on the given to-many path — "products without any image". Refused
     * on any other path, use `isNull()` for a single value.
     *
     * @return TExpression
     */
    public function isEmpty(string $path): object;

    /**
     * @return TExpression
     */
    public function isNotEmpty(string $path): object;

    /**
     * Negates a whole expression, which over a path leading through a to-many association means something
     * else than the negating operation of that expression, and both are legitimate questions:
     *
     *     not(equals('items.name', 'X'))  — no related row is named X
     *     notEquals('items.name', 'X')    — some related row is not named X
     *
     * A record with items named X and Y matches the second one and not the first. Pick knowingly.
     *
     * @param TExpression $expression
     * @return TExpression
     */
    public function not(object $expression): object;

    /**
     * @param TExpression ...$expressions No expression at all matches everything
     * @return TExpression
     */
    public function andX(object ...$expressions): object;

    /**
     * @param TExpression ...$expressions No expression at all matches nothing
     * @return TExpression
     */
    public function orX(object ...$expressions): object;
}
