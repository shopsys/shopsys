<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Closure;
use DateTimeInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Builds the expressions of data already in memory as callbacks deciding a single row.
 *
 * @extends \Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder<\Closure>
 */
class PhpExpressionBuilder extends AbstractExpressionBuilder
{
    public function __construct(
        protected readonly PhpPathAccessor $phpPathAccessor,
        protected readonly PhpValueComparator $phpValueComparator,
        ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
        parent::__construct($expressionOperatorEnum);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function compare(string $path, string $operator, mixed $value): Closure
    {
        $isEqual = fn (mixed $fieldValue): bool => $this->phpValueComparator->equals($fieldValue, $value, $operator);
        $isListed = fn (mixed $fieldValue): bool => array_any(
            $value,
            fn (mixed $listedValue): bool => $this->phpValueComparator->equals($fieldValue, $listedValue, $operator),
        );

        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => $this->buildEvaluator($path, $isEqual),
            ExpressionOperatorEnum::NOT_EQUALS => $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $isEqual($fieldValue) === false, matchesMissingValue: true),
            ExpressionOperatorEnum::CONTAINS => $this->buildTextSearch($path, $value, $this->phpValueComparator->contains(...)),
            ExpressionOperatorEnum::STARTS_WITH => $this->buildTextSearch($path, $value, $this->phpValueComparator->startsWith(...)),
            ExpressionOperatorEnum::ENDS_WITH => $this->buildTextSearch($path, $value, $this->phpValueComparator->endsWith(...)),
            ExpressionOperatorEnum::GREATER_THAN => $this->buildComparison($operator, $path, $value, static fn (int $comparison): bool => $comparison > 0),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => $this->buildComparison($operator, $path, $value, static fn (int $comparison): bool => $comparison >= 0),
            ExpressionOperatorEnum::LESS_THAN => $this->buildComparison($operator, $path, $value, static fn (int $comparison): bool => $comparison < 0),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => $this->buildComparison($operator, $path, $value, static fn (int $comparison): bool => $comparison <= 0),
            ExpressionOperatorEnum::BETWEEN => $this->buildBetween($path, $value[0], $value[1]),
            ExpressionOperatorEnum::IN => $this->buildEvaluator($path, $isListed),
            ExpressionOperatorEnum::NOT_IN => $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $isListed($fieldValue) === false, matchesMissingValue: true),
            // only a missing value matches
            ExpressionOperatorEnum::IS_NULL => $this->buildEvaluator($path, static fn (): bool => false, matchesMissingValue: true),
            ExpressionOperatorEnum::IS_NOT_NULL, ExpressionOperatorEnum::IS_NOT_EMPTY => $this->buildEvaluator($path, static fn (): bool => true),
            ExpressionOperatorEnum::IS_EMPTY => $this->not($this->buildEvaluator($path, static fn (): bool => true)),
            default => throw UnsupportedConditionException::operatorNotSupported($operator, static::class),
        };
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function not(object $expression): Closure
    {
        return static fn (array $row): bool => $expression($row) === false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function andX(object ...$expressions): Closure
    {
        return static fn (array $row): bool => array_all($expressions, static fn (Closure $evaluator): bool => $evaluator($row));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function orX(object ...$expressions): Closure
    {
        return static fn (array $row): bool => array_any($expressions, static fn (Closure $evaluator): bool => $evaluator($row));
    }

    /**
     * @param \Closure(mixed, string): bool $matchesText Decides whether the value of the record matches the normalized text
     */
    protected function buildTextSearch(string $path, string $text, Closure $matchesText): Closure
    {
        $normalizedText = $this->phpValueComparator->normalize($text);

        return $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $matchesText($fieldValue, $normalizedText));
    }

    /**
     * @param \Closure(int): bool $matchesComparison
     */
    protected function buildComparison(
        string $operator,
        string $path,
        string|int|float|DateTimeInterface|Money $value,
        Closure $matchesComparison,
    ): Closure {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $matchesComparison($this->phpValueComparator->compare($fieldValue, $value, $operator)));
    }

    protected function buildBetween(
        string $path,
        string|int|float|DateTimeInterface|Money $from,
        string|int|float|DateTimeInterface|Money $to,
    ): Closure {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($from, $to): bool {
            $fromComparison = $this->phpValueComparator->compare($fieldValue, $from, ExpressionOperatorEnum::BETWEEN);
            $toComparison = $this->phpValueComparator->compare($fieldValue, $to, ExpressionOperatorEnum::BETWEEN);

            return $fromComparison >= 0 && $toComparison <= 0;
        });
    }

    /**
     * A path crossing a collection leads to several values and the row matches when at least one of them does.
     *
     * @param \Closure(mixed): bool $matchesValue Decides a single value the path leads to, never a missing one
     * @param bool $matchesMissingValue Whether a missing value matches as well, as for `notEquals`, `notIn` and `isNull`
     * @return \Closure(array<string, mixed> $row): bool
     */
    protected function buildEvaluator(string $path, Closure $matchesValue, bool $matchesMissingValue = false): Closure
    {
        return fn (array $row): bool => array_any(
            $this->phpPathAccessor->read($row, $path),
            static fn (mixed $fieldValue): bool => $fieldValue === null ? $matchesMissingValue : $matchesValue($fieldValue),
        );
    }
}
