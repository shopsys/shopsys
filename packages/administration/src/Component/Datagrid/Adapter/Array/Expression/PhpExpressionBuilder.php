<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Closure;
use DateTimeInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Builds the expressions of data already in memory as callbacks deciding a single row.
 *
 * @implements \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<\Closure>
 */
final class PhpExpressionBuilder implements ExpressionBuilderInterface
{
    /**
     * Data in memory are compared by plain PHP, which expresses every operation of the vocabulary.
     *
     * @var string[]
     */
    private const array SUPPORTED_OPERATORS = [
        ExpressionOperatorEnum::EQUALS,
        ExpressionOperatorEnum::NOT_EQUALS,
        ExpressionOperatorEnum::CONTAINS,
        ExpressionOperatorEnum::STARTS_WITH,
        ExpressionOperatorEnum::ENDS_WITH,
        ExpressionOperatorEnum::GREATER_THAN,
        ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL,
        ExpressionOperatorEnum::LESS_THAN,
        ExpressionOperatorEnum::LESS_THAN_OR_EQUAL,
        ExpressionOperatorEnum::BETWEEN,
        ExpressionOperatorEnum::IN,
        ExpressionOperatorEnum::NOT_IN,
        ExpressionOperatorEnum::IS_NULL,
        ExpressionOperatorEnum::IS_NOT_NULL,
        ExpressionOperatorEnum::IS_EMPTY,
        ExpressionOperatorEnum::IS_NOT_EMPTY,
    ];

    public function __construct(
        private readonly PhpPathAccessor $phpPathAccessor,
        private readonly PhpValueComparator $phpValueComparator,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function equals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Closure
    {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->equals($fieldValue, $value));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function notEquals(string $path, string|int|float|bool|DateTimeInterface|Money $value): Closure
    {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->equals($fieldValue, $value) === false);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function contains(string $path, string $value): Closure
    {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->contains($fieldValue, $value));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function startsWith(string $path, string $value): Closure
    {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->startsWith($fieldValue, $value));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function endsWith(string $path, string $value): Closure
    {
        return $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->endsWith($fieldValue, $value));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function greaterThan(string $path, string|int|float|DateTimeInterface|Money $value): Closure
    {
        return $this->buildComparison($path, $value, fn (int $comparison): bool => $comparison > 0);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function greaterThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): Closure
    {
        return $this->buildComparison($path, $value, fn (int $comparison): bool => $comparison >= 0);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function lessThan(string $path, string|int|float|DateTimeInterface|Money $value): Closure
    {
        return $this->buildComparison($path, $value, fn (int $comparison): bool => $comparison < 0);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function lessThanOrEqual(string $path, string|int|float|DateTimeInterface|Money $value): Closure
    {
        return $this->buildComparison($path, $value, fn (int $comparison): bool => $comparison <= 0);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function between(
        string $path,
        string|int|float|DateTimeInterface|Money $from,
        string|int|float|DateTimeInterface|Money $to,
    ): Closure {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($from, $to): bool {
            $fromComparison = $this->phpValueComparator->compare($fieldValue, $from);
            $toComparison = $this->phpValueComparator->compare($fieldValue, $to);

            return $fromComparison !== null && $toComparison !== null && $fromComparison >= 0 && $toComparison <= 0;
        });
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function in(string $path, array $values): Closure
    {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($values): bool {
            foreach ($values as $value) {
                if ($this->phpValueComparator->equals($fieldValue, $value)) {
                    return true;
                }
            }

            return false;
        });
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function notIn(string $path, array $values): Closure
    {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($values): bool {
            foreach ($values as $value) {
                if ($this->phpValueComparator->equals($fieldValue, $value)) {
                    return false;
                }
            }

            return true;
        });
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNull(string $path): Closure
    {
        return $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $fieldValue === null);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNotNull(string $path): Closure
    {
        return $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $fieldValue !== null);
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isEmpty(string $path): Closure
    {
        // an empty collection yields no value at all, unlike a missing single value which yields null
        return fn (array $row): bool => $this->phpPathAccessor->read($row, $path) === [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function isNotEmpty(string $path): Closure
    {
        return fn (array $row): bool => $this->phpPathAccessor->read($row, $path) !== [];
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function not(object $expression): Closure
    {
        $evaluator = $this->assertExpression($expression);

        return static fn (array $row): bool => $evaluator($row) === false;
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function andX(object ...$expressions): Closure
    {
        $evaluators = array_map($this->assertExpression(...), $expressions);

        return static function (array $row) use ($evaluators): bool {
            foreach ($evaluators as $evaluator) {
                if ($evaluator($row) === false) {
                    return false;
                }
            }

            return true;
        };
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function orX(object ...$expressions): Closure
    {
        $evaluators = array_map($this->assertExpression(...), $expressions);

        return static function (array $row) use ($evaluators): bool {
            foreach ($evaluators as $evaluator) {
                if ($evaluator($row)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function supportsOperator(string $operator): bool
    {
        return in_array($operator, self::SUPPORTED_OPERATORS, true);
    }

    /**
     * @param \Closure(int): bool $matchesComparison Decides the result of comparing the value of the record with the value of the expression
     */
    private function buildComparison(
        string $path,
        string|int|float|DateTimeInterface|Money $value,
        Closure $matchesComparison,
    ): Closure {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($value, $matchesComparison): bool {
            $comparison = $this->phpValueComparator->compare($fieldValue, $value);

            return $comparison !== null && $matchesComparison($comparison);
        });
    }

    /**
     * A path crossing a collection leads to several values and the row matches when at least one of them
     * does, which is the same semantics a query gets from its subquery.
     *
     * @param \Closure(mixed): bool $matchesValue Decides a single value the path leads to
     * @return \Closure(array<string, mixed> $row): bool
     */
    private function buildEvaluator(string $path, Closure $matchesValue): Closure
    {
        return function (array $row) use ($path, $matchesValue): bool {
            foreach ($this->phpPathAccessor->read($row, $path) as $fieldValue) {
                if ($matchesValue($fieldValue)) {
                    return true;
                }
            }

            return false;
        };
    }

    private function assertExpression(object $expression): Closure
    {
        if ($expression instanceof Closure === false) {
            throw new ExpressionFromForeignMediumException(Closure::class, $expression, self::class);
        }

        return $expression;
    }
}
