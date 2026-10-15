<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\Expression;

use Closure;
use DateTimeInterface;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Orx;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\ProxyQuery;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Stringable;

/**
 * Builds the expressions of a Doctrine query. The builder is bound to the query it builds for, because
 * an expression on a path leading through an association needs that association joined.
 *
 * A value is registered as a parameter of the query right away, so an expression that is built and then
 * thrown away leaves an unused parameter behind and Doctrine refuses such a query. That is the behaviour
 * of `QueryBuilder::expr()` with `setParameter()` as well — build only the expressions you apply, which the
 * adapter guarantees by compiling the condition on demand.
 *
 * SQL evaluates a comparison with a missing value as unknown and leaves the record out even when the comparison
 * is negated, so a negation tests the missing value explicitly — a record without a value is "not X" in every
 * medium.
 *
 * A project teaching the medium an operation of its own extends the builder, expresses the operation in `compare()`
 * and hands every other one to `parent::compare()`.
 *
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder<\Stringable>
 */
class DqlExpressionBuilder extends AbstractExpressionBuilder implements DqlExpressionBuilderInterface
{
    /**
     * PostgreSQL escapes the wildcards of `LIKE` by a backslash unless told otherwise.
     */
    protected const string LIKE_ESCAPE_CHARACTER = '\\';

    /**
     * DQL has no boolean literal, so the predicates matching everything and nothing are expressed
     * by a comparison that is always true, resp. always false.
     */
    protected const string MATCHES_EVERYTHING = '1 = 1';

    protected const string MATCHES_NOTHING = '1 = 0';

    public function __construct(
        protected readonly ProxyQuery $proxyQuery,
        ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
        parent::__construct($expressionOperatorEnum);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function dql(Closure $buildPredicate): Stringable
    {
        return $this->toExpression($buildPredicate($this->proxyQuery));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function compare(string $path, string $operator, mixed $value): Stringable
    {
        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => $this->buildComparison($operator, $path, '=', $value),
            ExpressionOperatorEnum::NOT_EQUALS => $this->buildComparison($operator, $path, '<>', $value, true),
            ExpressionOperatorEnum::CONTAINS => $this->buildLike($operator, $path, '%' . $this->escapeLikeValue($value) . '%'),
            ExpressionOperatorEnum::STARTS_WITH => $this->buildLike($operator, $path, $this->escapeLikeValue($value) . '%'),
            ExpressionOperatorEnum::ENDS_WITH => $this->buildLike($operator, $path, '%' . $this->escapeLikeValue($value)),
            ExpressionOperatorEnum::GREATER_THAN => $this->buildComparison($operator, $path, '>', $value),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => $this->buildComparison($operator, $path, '>=', $value),
            ExpressionOperatorEnum::LESS_THAN => $this->buildComparison($operator, $path, '<', $value),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => $this->buildComparison($operator, $path, '<=', $value),
            ExpressionOperatorEnum::BETWEEN => $this->buildBetween($path, $value[0], $value[1]),
            // an empty list matches nothing, resp. everything, which SQL `IN ()` cannot express
            ExpressionOperatorEnum::IN => $this->buildList($operator, $path, 'IN', $value, static::MATCHES_NOTHING),
            ExpressionOperatorEnum::NOT_IN => $this->buildList($operator, $path, 'NOT IN', $value, static::MATCHES_EVERYTHING, true),
            ExpressionOperatorEnum::IS_NULL => $this->buildPredicate($operator, $path, static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NULL'),
            ExpressionOperatorEnum::IS_NOT_NULL => $this->buildPredicate($operator, $path, static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NOT NULL'),
            // the subquery never evaluates as unknown, so a plain negation is enough
            ExpressionOperatorEnum::IS_EMPTY => $this->toExpression(sprintf('NOT (%s)', $this->buildExistsPredicate($operator, $path))),
            ExpressionOperatorEnum::IS_NOT_EMPTY => $this->toExpression($this->buildExistsPredicate($operator, $path)),
            default => throw new OperatorNotSupportedException($operator, static::class),
        };
    }

    /**
     * A plain `NOT` of an expression evaluated as unknown is unknown as well, so the expression is first turned
     * into a number that is never unknown.
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
            return $this->toExpression(static::MATCHES_EVERYTHING);
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
            return $this->toExpression(static::MATCHES_NOTHING);
        }

        return new Orx($expressions);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function assertExpression(object $expression): Stringable
    {
        if ($expression instanceof Stringable === false) {
            throw new ExpressionFromForeignMediumException(Stringable::class, $expression, static::class);
        }

        return $expression;
    }

    /**
     * @param bool $matchesMissingValue Whether a record without a value matches as well, which a negation does
     */
    protected function buildComparison(
        string $operator,
        string $path,
        string $comparisonOperator,
        string|int|float|bool|DateTimeInterface|Money $value,
        bool $matchesMissingValue = false,
    ): Stringable {
        return $this->buildPredicate($operator, $path, fn (PathResolution $pathResolution): string => $this->orMissing(
            sprintf(
                '%s %s :%s',
                $pathResolution->dqlExpression,
                $comparisonOperator,
                $this->proxyQuery->addParameter($value, $pathResolution->fieldType),
            ),
            $pathResolution,
            $matchesMissingValue,
        ));
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
     * Every value gets a parameter of its own bound with the type of the compared field — a list parameter
     * of Doctrine binds only integers and strings, so a boolean, a date or money would never match.
     *
     * @param list<string|int|float|bool|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money> $values
     * @param string $matchesEmptyList The predicate an empty list stands for
     * @param bool $matchesMissingValue Whether a record without a value matches as well, which a negation does
     */
    protected function buildList(
        string $operator,
        string $path,
        string $listOperator,
        array $values,
        string $matchesEmptyList,
        bool $matchesMissingValue = false,
    ): Stringable {
        if ($values === []) {
            return $this->toExpression($matchesEmptyList);
        }

        return $this->buildPredicate($operator, $path, fn (PathResolution $pathResolution): string => $this->orMissing(
            sprintf(
                '%s %s (%s)',
                $pathResolution->dqlExpression,
                $listOperator,
                implode(', ', array_map(
                    fn (mixed $value): string => ':' . $this->proxyQuery->addParameter($value, $pathResolution->fieldType),
                    $values,
                )),
            ),
            $pathResolution,
            $matchesMissingValue,
        ));
    }

    protected function orMissing(string $predicate, PathResolution $pathResolution, bool $matchesMissingValue): string
    {
        return $matchesMissingValue ? sprintf('(%s OR %s IS NULL)', $predicate, $pathResolution->dqlExpression) : $predicate;
    }

    /**
     * Both sides go through the `NORMALIZED()` function of the database (lower case, no diacritics), so that
     * the administrator never has to guess the case or the accents of the stored value — "hrnicek" finds
     * "Hrníček", the same way the quick search of the administration always did. Normalizing the value in
     * the database rather than in PHP keeps both sides of the comparison under one definition.
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
     * The applicability of the operation is checked on the resolved path first, so that an operation which
     * would fail in the database or quietly match nothing is refused with a readable reason instead.
     *
     * A path leading through a to-many association is matched by a correlated subquery, so that the listed
     * record is never multiplied by the number of its related rows.
     *
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string $buildPredicate
     */
    protected function buildPredicate(string $operator, string $path, Closure $buildPredicate): Stringable
    {
        $pathResolution = $this->proxyQuery->resolvePath($path);
        $this->expressionOperatorEnum->assertApplicable($operator, $pathResolution->description);

        if ($pathResolution->isToMany()) {
            return $this->toExpression($this->proxyQuery->exists($path, $buildPredicate));
        }

        return $this->toExpression($buildPredicate($pathResolution));
    }

    protected function buildExistsPredicate(string $operator, string $path): string
    {
        $this->expressionOperatorEnum->assertApplicable($operator, $this->proxyQuery->resolvePath($path)->description);

        return $this->proxyQuery->exists($path);
    }

    /**
     * Doctrine expresses some predicates as a plain string, so every predicate is wrapped in a composite
     * of a single part — it renders as the bare predicate and gives the medium one expression type.
     */
    protected function toExpression(string $dql): Stringable
    {
        return new Andx([$dql]);
    }

    protected function escapeLikeValue(string $value): string
    {
        $escapeCharacter = static::LIKE_ESCAPE_CHARACTER;

        return str_replace(
            [$escapeCharacter, '%', '_'],
            [$escapeCharacter . $escapeCharacter, $escapeCharacter . '%', $escapeCharacter . '_'],
            $value,
        );
    }
}
