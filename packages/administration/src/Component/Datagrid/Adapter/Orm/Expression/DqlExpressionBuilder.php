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
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
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
 * adapter guarantees by building them on demand.
 */
final class DqlExpressionBuilder implements DqlExpressionBuilderInterface
{
    /**
     * PostgreSQL escapes the wildcards of `LIKE` by a backslash unless told otherwise.
     */
    private const string LIKE_ESCAPE_CHARACTER = '\\';

    /**
     * DQL has no boolean literal, so the predicates matching everything and nothing are expressed
     * by a comparison that is always true, resp. always false.
     */
    private const string MATCHES_EVERYTHING = '1 = 1';

    private const string MATCHES_NOTHING = '1 = 0';

    /**
     * @param string[] $supportedOperators
     */
    public function __construct(
        private readonly ProxyQuery $proxyQuery,
        private readonly array $supportedOperators,
        private readonly ExpressionOperatorApplicability $expressionOperatorApplicability,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function equals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::EQUALS, $path, '=', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function notEquals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::NOT_EQUALS, $path, '<>', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function contains(string $path, string $value): Stringable
    {
        return $this->buildLike(ExpressionOperatorEnum::CONTAINS, $path, '%' . $this->escapeLikeValue($value) . '%');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function startsWith(string $path, string $value): Stringable
    {
        return $this->buildLike(ExpressionOperatorEnum::STARTS_WITH, $path, $this->escapeLikeValue($value) . '%');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function endsWith(string $path, string $value): Stringable
    {
        return $this->buildLike(ExpressionOperatorEnum::ENDS_WITH, $path, '%' . $this->escapeLikeValue($value));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function greaterThan(string $path, string|int|float|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::GREATER_THAN, $path, '>', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function greaterThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL, $path, '>=', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function lessThan(string $path, string|int|float|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::LESS_THAN, $path, '<', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function lessThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): Stringable
    {
        return $this->buildComparison(ExpressionOperatorEnum::LESS_THAN_OR_EQUAL, $path, '<=', $value);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function between(
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
     * {@inheritdoc}
     */
    #[Override]
    public function in(string $path, array $values): Stringable
    {
        // an empty list matches nothing, which SQL `IN ()` cannot express
        if ($values === []) {
            return $this->toExpression(self::MATCHES_NOTHING);
        }

        return $this->buildPredicate(ExpressionOperatorEnum::IN, $path, fn (PathResolution $pathResolution): string => sprintf(
            '%s IN (:%s)',
            $pathResolution->dqlExpression,
            $this->proxyQuery->addParameter($values),
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function notIn(string $path, array $values): Stringable
    {
        if ($values === []) {
            return $this->toExpression(self::MATCHES_EVERYTHING);
        }

        return $this->buildPredicate(ExpressionOperatorEnum::NOT_IN, $path, fn (PathResolution $pathResolution): string => sprintf(
            '%s NOT IN (:%s)',
            $pathResolution->dqlExpression,
            $this->proxyQuery->addParameter($values),
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNull(string $path): Stringable
    {
        return $this->buildPredicate(ExpressionOperatorEnum::IS_NULL, $path, static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NULL');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNotNull(string $path): Stringable
    {
        return $this->buildPredicate(ExpressionOperatorEnum::IS_NOT_NULL, $path, static fn (PathResolution $pathResolution): string => $pathResolution->dqlExpression . ' IS NOT NULL');
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isEmpty(string $path): Stringable
    {
        return $this->toExpression(sprintf('NOT (%s)', $this->buildExistsPredicate(ExpressionOperatorEnum::IS_EMPTY, $path)));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNotEmpty(string $path): Stringable
    {
        return $this->toExpression($this->buildExistsPredicate(ExpressionOperatorEnum::IS_NOT_EMPTY, $path));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function not(object $expression): Stringable
    {
        return $this->toExpression(sprintf('NOT (%s)', $this->assertExpression($expression)));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function andX(object ...$expressions): Stringable
    {
        if ($expressions === []) {
            return $this->toExpression(self::MATCHES_EVERYTHING);
        }

        return new Andx(array_map($this->assertExpression(...), $expressions));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function orX(object ...$expressions): Stringable
    {
        if ($expressions === []) {
            return $this->toExpression(self::MATCHES_NOTHING);
        }

        return new Orx(array_map($this->assertExpression(...), $expressions));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function dql(Closure $buildPredicate): Stringable
    {
        return $this->toExpression($buildPredicate($this->proxyQuery->getQueryBuilder(), $this->proxyQuery));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supportsOperator(string $operator): bool
    {
        return in_array($operator, $this->supportedOperators, true);
    }

    private function buildComparison(
        string $operator,
        string $path,
        string $comparisonOperator,
        string|int|float|bool|DateTimeInterface|Money $value,
    ): Stringable {
        return $this->buildPredicate($operator, $path, fn (PathResolution $pathResolution): string => sprintf(
            '%s %s :%s',
            $pathResolution->dqlExpression,
            $comparisonOperator,
            $this->proxyQuery->addParameter($value, $pathResolution->fieldType),
        ));
    }

    /**
     * Both sides go through the `NORMALIZED()` function of the database (lower case, no diacritics), so that
     * the administrator never has to guess the case or the accents of the stored value — "hrnicek" finds
     * "Hrníček", the same way the quick search of the administration always did. Normalizing the value in
     * the database rather than in PHP keeps both sides of the comparison under one definition, and lets
     * the `NORMALIZED(column)` indexes of the schema serve the prefix patterns.
     */
    private function buildLike(string $operator, string $path, string $pattern): Stringable
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
     * @param \Closure(\Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Orm\PathResolution): string $buildPredicate
     */
    private function buildPredicate(string $operator, string $path, Closure $buildPredicate): Stringable
    {
        return $this->toExpression($this->proxyQuery->buildPredicate(
            $path,
            function (PathResolution $pathResolution) use ($operator, $buildPredicate): string {
                $this->expressionOperatorApplicability->assertApplicable($operator, $pathResolution->description);

                return $buildPredicate($pathResolution);
            },
        ));
    }

    private function buildExistsPredicate(string $operator, string $path): string
    {
        $this->expressionOperatorApplicability->assertApplicable($operator, $this->proxyQuery->describePath($path));

        return $this->proxyQuery->buildExistsPredicate($path);
    }

    /**
     * Doctrine expresses some predicates as a plain string, so every predicate is wrapped in a composite
     * of a single part — it renders as the bare predicate and gives the medium one expression type.
     */
    private function toExpression(string $dql): Stringable
    {
        return new Andx([$dql]);
    }

    private function escapeLikeValue(string $value): string
    {
        $escapeCharacter = self::LIKE_ESCAPE_CHARACTER;

        return str_replace(
            [$escapeCharacter, '%', '_'],
            [$escapeCharacter . $escapeCharacter, $escapeCharacter . '%', $escapeCharacter . '_'],
            $value,
        );
    }

    private function assertExpression(object $expression): Stringable
    {
        if ($expression instanceof Stringable === false) {
            throw new ExpressionFromForeignMediumException(Stringable::class, $expression, self::class);
        }

        return $expression;
    }
}
