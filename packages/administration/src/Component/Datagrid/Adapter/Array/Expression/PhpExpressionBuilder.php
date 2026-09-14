<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Adapter\Array\Expression;

use Closure;
use DateTimeInterface;
use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\AbstractExpressionBuilder;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Money\Money;

/**
 * Builds the expressions of data already in memory as callbacks deciding a single row. A missing value never
 * matches a comparison and always matches its negation, the same way the query of the ORM adapter decides it.
 *
 * A project teaching the medium an operation of its own extends the builder, expresses the operation in `compare()`
 * and hands every other one to `parent::compare()`.
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
        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->equals($fieldValue, $value)),
            ExpressionOperatorEnum::NOT_EQUALS => $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->equals($fieldValue, $value) === false),
            ExpressionOperatorEnum::CONTAINS => $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->contains($fieldValue, $value)),
            ExpressionOperatorEnum::STARTS_WITH => $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->startsWith($fieldValue, $value)),
            ExpressionOperatorEnum::ENDS_WITH => $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->phpValueComparator->endsWith($fieldValue, $value)),
            ExpressionOperatorEnum::GREATER_THAN => $this->buildComparison($path, $value, static fn (int $comparison): bool => $comparison > 0),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => $this->buildComparison($path, $value, static fn (int $comparison): bool => $comparison >= 0),
            ExpressionOperatorEnum::LESS_THAN => $this->buildComparison($path, $value, static fn (int $comparison): bool => $comparison < 0),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => $this->buildComparison($path, $value, static fn (int $comparison): bool => $comparison <= 0),
            ExpressionOperatorEnum::BETWEEN => $this->buildBetween($path, $value[0], $value[1]),
            // an empty list matches nothing, resp. everything, whatever the path leads to — over a collection as well
            ExpressionOperatorEnum::IN => $value === [] ? static fn (array $row): bool => false : $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->isInList($fieldValue, $value)),
            ExpressionOperatorEnum::NOT_IN => $value === [] ? static fn (array $row): bool => true : $this->buildEvaluator($path, fn (mixed $fieldValue): bool => $this->isInList($fieldValue, $value) === false),
            ExpressionOperatorEnum::IS_NULL => $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $fieldValue === null),
            ExpressionOperatorEnum::IS_NOT_NULL => $this->buildEvaluator($path, static fn (mixed $fieldValue): bool => $fieldValue !== null),
            ExpressionOperatorEnum::IS_EMPTY => fn (array $row): bool => $this->readPresentValues($row, $path) === [],
            ExpressionOperatorEnum::IS_NOT_EMPTY => fn (array $row): bool => $this->readPresentValues($row, $path) !== [],
            default => throw new OperatorNotSupportedException($operator, static::class),
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
        return static function (array $row) use ($expressions): bool {
            foreach ($expressions as $evaluator) {
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
    protected function orX(object ...$expressions): Closure
    {
        return static function (array $row) use ($expressions): bool {
            foreach ($expressions as $evaluator) {
                if ($evaluator($row)) {
                    return true;
                }
            }

            return false;
        };
    }

    /**
     * @param \Closure(int): bool $matchesComparison Decides the result of comparing the value of the record with the value of the expression
     */
    protected function buildComparison(
        string $path,
        string|int|float|DateTimeInterface|Money $value,
        Closure $matchesComparison,
    ): Closure {
        return $this->buildEvaluator($path, function (mixed $fieldValue) use ($value, $matchesComparison): bool {
            $comparison = $this->phpValueComparator->compare($fieldValue, $value);

            return $comparison !== null && $matchesComparison($comparison);
        });
    }

    protected function buildBetween(
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
     * @param list<string|int|float|bool|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money> $values
     */
    protected function isInList(mixed $fieldValue, array $values): bool
    {
        foreach ($values as $value) {
            if ($this->phpValueComparator->equals($fieldValue, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A path crossing a collection leads to several values and the row matches when at least one of them
     * does, which is the same semantics a query gets from its subquery.
     *
     * @param \Closure(mixed): bool $matchesValue Decides a single value the path leads to
     * @return \Closure(array<string, mixed> $row): bool
     */
    protected function buildEvaluator(string $path, Closure $matchesValue): Closure
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

    /**
     * An empty collection yields no value at all, and a related row missing the value (an order item without
     * a product) has none to count either — the same way a query decides it.
     *
     * @param array<string, mixed> $row
     * @return list<mixed>
     */
    protected function readPresentValues(array $row, string $path): array
    {
        return array_values(array_filter(
            $this->phpPathAccessor->read($row, $path),
            static fn (mixed $fieldValue): bool => $fieldValue !== null,
        ));
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    protected function assertExpression(object $expression): Closure
    {
        if ($expression instanceof Closure === false) {
            throw new ExpressionFromForeignMediumException(Closure::class, $expression, static::class);
        }

        return $expression;
    }
}
