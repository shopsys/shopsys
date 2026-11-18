<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Closure;
use DateTimeInterface;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Condition\DqlCondition;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Stringable;

/**
 * Builds the DQL expressions of one Doctrine query:
 *
 * - The builder is bound to its query, because a path through an association needs that association joined.
 * - A value becomes a parameter of the query right away, so build only the expressions you apply. Doctrine refuses
 *   a query with an unused parameter, the same as with `QueryBuilder::expr()` and `setParameter()`.
 * - SQL evaluates a comparison with NULL as unknown, and `NOT` keeps it unknown. A negation therefore tests
 *   the missing value explicitly — `notEquals` → `(o.name <> :dgexpr_0 OR o.name IS NULL)`.
 *
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder<\Stringable>
 * @phpstan-import-type ScalarValue from \Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition
 */
class DqlExpressionBuilder extends AbstractExpressionBuilder
{
    /**
     * The default escape character of a PostgreSQL `LIKE` pattern — `50%` is searched as `50\%`.
     * The expression names no `ESCAPE` character, as Doctrine would need a database connection to quote it,
     * so the character is fixed.
     */
    protected const string LIKE_ESCAPE_CHARACTER = '\\';

    public function __construct(
        protected readonly ProxyQuery $proxyQuery,
        ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
        parent::__construct($expressionOperatorEnum);
    }

    /**
     * A `DqlCondition` builds its predicate from the query of this builder, at any depth of the tree.
     */
    #[Override]
    public function compile(ConditionInterface $condition): Stringable
    {
        if ($condition instanceof DqlCondition) {
            return $this->toExpression(($condition->buildPredicate)($this->proxyQuery));
        }

        return parent::compile($condition);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function compare(string $path, string $operator, mixed $value): Stringable
    {
        $isNotNull = static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NOT NULL';

        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => $this->buildComparison($operator, $path, '=', $value),
            ExpressionOperatorEnum::NOT_EQUALS => $this->buildComparison($operator, $path, '<>', $value, matchesMissingValue: true),
            ExpressionOperatorEnum::CONTAINS => $this->buildLike($operator, $path, '%' . $this->escapeLikeValue($value) . '%'),
            ExpressionOperatorEnum::STARTS_WITH => $this->buildLike($operator, $path, $this->escapeLikeValue($value) . '%'),
            ExpressionOperatorEnum::ENDS_WITH => $this->buildLike($operator, $path, '%' . $this->escapeLikeValue($value)),
            ExpressionOperatorEnum::GREATER_THAN => $this->buildComparison($operator, $path, '>', $value),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => $this->buildComparison($operator, $path, '>=', $value),
            ExpressionOperatorEnum::LESS_THAN => $this->buildComparison($operator, $path, '<', $value),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => $this->buildComparison($operator, $path, '<=', $value),
            ExpressionOperatorEnum::BETWEEN => $this->buildBetween($path, $value[0], $value[1]),
            ExpressionOperatorEnum::IN => $this->buildList($operator, $path, 'IN', $value),
            ExpressionOperatorEnum::NOT_IN => $this->buildList($operator, $path, 'NOT IN', $value, matchesMissingValue: true),
            ExpressionOperatorEnum::IS_NULL => $this->buildPredicate($operator, $path, static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NULL'),
            ExpressionOperatorEnum::IS_NOT_NULL, ExpressionOperatorEnum::IS_NOT_EMPTY => $this->buildPredicate($operator, $path, $isNotNull),
            // - a related row counts only with a value on the path — `items.product isEmpty` requires no row with
            //   `dgsub_0_items__product_join IS NOT NULL`; the subquery joins a to-one association by `LEFT JOIN`, so
            //   an order item without a product would count otherwise
            // - the subquery never evaluates as unknown, so a plain negation is enough
            ExpressionOperatorEnum::IS_EMPTY => $this->toExpression(sprintf('NOT (%s)', $this->buildPredicate($operator, $path, $isNotNull))),
            default => throw UnsupportedConditionException::operatorNotSupported($operator, static::class),
        };
    }

    /**
     * The negation compares a number, which is never unknown — `CASE WHEN (o.name = :dgexpr_0) THEN 1 ELSE 0 END = 0`.
     */
    #[Override]
    protected function not(object $expression): Stringable
    {
        return $this->toExpression(sprintf('CASE WHEN (%s) THEN 1 ELSE 0 END = 0', $expression));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function andX(object ...$expressions): Stringable
    {
        if ($expressions === []) {
            // a DQL condition cannot be a bare TRUE literal
            return $this->toExpression('1 = 1');
        }

        return new Andx($expressions);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function orX(object ...$expressions): Stringable
    {
        if ($expressions === []) {
            // a DQL condition cannot be a bare FALSE literal
            return $this->toExpression('1 = 0');
        }

        return new Orx($expressions);
    }

    protected function buildComparison(
        string $operator,
        string $path,
        string $comparisonOperator,
        string|int|float|bool|DateTimeInterface|Money $value,
        bool $matchesMissingValue = false,
    ): Stringable {
        return $this->buildPredicate(
            $operator,
            $path,
            fn (PathResolution $pathResolution): string => sprintf(
                '%s %s :%s',
                $pathResolution->dqlExpression,
                $comparisonOperator,
                $this->proxyQuery->addParameter($value, $pathResolution->fieldType),
            ),
            matchesMissingValue: $matchesMissingValue,
        );
    }

    protected function buildBetween(
        string $path,
        string|int|float|DateTimeInterface|Money $from,
        string|int|float|DateTimeInterface|Money $to,
    ): Stringable {
        return $this->buildPredicate(ExpressionOperatorEnum::BETWEEN, $path, fn (PathResolution $pathResolution): string => sprintf(
            '%s BETWEEN :%s AND :%s',
            $pathResolution->dqlExpression,
            $this->proxyQuery->addParameter($from, $pathResolution->fieldType),
            $this->proxyQuery->addParameter($to, $pathResolution->fieldType),
        ));
    }

    /**
     * Every value gets a parameter of its own, bound with the type of the compared field —
     * `o.status IN (:dgexpr_0, :dgexpr_1)`. A list parameter of Doctrine binds only integers and strings, so
     * a boolean, a date or money would never match.
     *
     * @param list<ScalarValue> $values
     */
    protected function buildList(
        string $operator,
        string $path,
        string $listOperator,
        array $values,
        bool $matchesMissingValue = false,
    ): Stringable {
        return $this->buildPredicate(
            $operator,
            $path,
            fn (PathResolution $pathResolution): string => sprintf(
                '%s %s (%s)',
                $pathResolution->dqlExpression,
                $listOperator,
                implode(', ', array_map(
                    fn (mixed $value): string => ':' . $this->proxyQuery->addParameter($value, $pathResolution->fieldType),
                    $values,
                )),
            ),
            matchesMissingValue: $matchesMissingValue,
        );
    }

    /**
     * Both sides go through the `NORMALIZED()` database function (lower case, no diacritics), so they follow one
     * definition and "hrnicek" finds "Hrníček" — `NORMALIZED(o.name) LIKE NORMALIZED(:dgexpr_0)`.
     */
    protected function buildLike(string $operator, string $path, string $pattern): Stringable
    {
        return $this->buildPredicate($operator, $path, fn (PathResolution $pathResolution): string => sprintf(
            'NORMALIZED(%s) LIKE NORMALIZED(:%s)',
            $pathResolution->dqlExpression,
            $this->proxyQuery->addParameter($pattern),
        ));
    }

    /**
     * The operation is checked to be applicable to the path first, so a wrong operation fails with a readable
     * reason rather than in the database.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string $buildPredicate
     * @param bool $matchesMissingValue Whether a record without a value matches as well, as for `notEquals` and `notIn`
     */
    protected function buildPredicate(
        string $operator,
        string $path,
        Closure $buildPredicate,
        bool $matchesMissingValue = false,
    ): Stringable {
        $pathResolution = $this->proxyQuery->resolvePath($path);
        $this->expressionOperatorEnum->assertApplicable($operator, $pathResolution->description);

        $buildMatchingPredicate = $matchesMissingValue
            ? static fn (PathResolution $pathResolution): string => sprintf('(%s OR %s IS NULL)', $buildPredicate($pathResolution), $pathResolution->dqlExpression)
            : $buildPredicate;

        if ($pathResolution->isToMany()) {
            return $this->toExpression($this->proxyQuery->exists($path, $buildMatchingPredicate));
        }

        return $this->toExpression($buildMatchingPredicate($pathResolution));
    }

    /**
     * Wraps a predicate in an `Andx` of a single part, which renders as the bare predicate.
     * Doctrine expresses some predicates as a plain string, the wrapper gives the builder one expression type.
     */
    protected function toExpression(string $dql): Stringable
    {
        return new Andx([$dql]);
    }

    protected function escapeLikeValue(string $value): string
    {
        $escapeCharacter = self::LIKE_ESCAPE_CHARACTER;

        return str_replace(
            [$escapeCharacter, '%', '_'],
            [$escapeCharacter . $escapeCharacter, $escapeCharacter . '%', $escapeCharacter . '_'],
            $value,
        );
    }
}
