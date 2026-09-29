<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Composite;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\LogicalOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\MediumSpecificConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Negation;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException;
use Webmozart\Assert\Assert;

/**
 * The one place where the vocabulary of the conditions meets the methods expressing them, so that every
 * medium shares one mapping instead of repeating its own.
 */
final class ExpressionCompiler implements ExpressionCompilerInterface
{
    public function __construct(
        private readonly ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function compile(ExpressionBuilderInterface $expressionBuilder, ConditionInterface $condition): object
    {
        return match (true) {
            $condition instanceof Comparison => $this->compileComparison($expressionBuilder, $condition),
            $condition instanceof Composite => $this->compileComposite($expressionBuilder, $condition),
            $condition instanceof Negation => $expressionBuilder->not($this->compile($expressionBuilder, $condition->condition)),
            $condition instanceof MediumSpecificConditionInterface => $condition->compile($expressionBuilder),
            default => throw new ConditionNotSupportedException($condition::class, self::class),
        };
    }

    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @return TExpression
     */
    private function compileComposite(ExpressionBuilderInterface $expressionBuilder, Composite $composite): object
    {
        $expressions = array_map(
            fn (ConditionInterface $condition): object => $this->compile($expressionBuilder, $condition),
            $composite->conditions,
        );

        return match ($composite->operator) {
            LogicalOperatorEnum::AND => $expressionBuilder->andX(...$expressions),
            LogicalOperatorEnum::OR => $expressionBuilder->orX(...$expressions),
        };
    }

    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @return TExpression
     */
    private function compileComparison(ExpressionBuilderInterface $expressionBuilder, Comparison $comparison): object
    {
        $operator = $comparison->operator;
        $path = $comparison->path;
        $value = $comparison->value;

        $this->assertOperatorIsSupported($expressionBuilder, $operator);
        $this->assertValueMatchesArity($operator, $value);

        // the operations are grouped by the shape of the value they compare, which is the only thing
        // that differs between them from here on
        return match ($this->expressionOperatorEnum->getValueArity($operator)) {
            ExpressionValueArityEnum::NONE => $this->compileWithoutValue($expressionBuilder, $operator, $path),
            ExpressionValueArityEnum::LIST => $this->compileWithList($expressionBuilder, $operator, $path, $value),
            ExpressionValueArityEnum::RANGE => $expressionBuilder->between($path, $value[0], $value[1]),
            default => $this->compileWithSingleValue($expressionBuilder, $operator, $path, $value),
        };
    }

    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @return TExpression
     */
    private function compileWithoutValue(
        ExpressionBuilderInterface $expressionBuilder,
        string $operator,
        string $path,
    ): object {
        return match ($operator) {
            ExpressionOperatorEnum::IS_NULL => $expressionBuilder->isNull($path),
            ExpressionOperatorEnum::IS_NOT_NULL => $expressionBuilder->isNotNull($path),
            ExpressionOperatorEnum::IS_EMPTY => $expressionBuilder->isEmpty($path),
            ExpressionOperatorEnum::IS_NOT_EMPTY => $expressionBuilder->isNotEmpty($path),
            default => throw new OperatorNotSupportedException($operator, $expressionBuilder::class),
        };
    }

    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @param list<mixed> $values
     * @return TExpression
     */
    private function compileWithList(
        ExpressionBuilderInterface $expressionBuilder,
        string $operator,
        string $path,
        array $values,
    ): object {
        return match ($operator) {
            ExpressionOperatorEnum::IN => $expressionBuilder->in($path, $values),
            ExpressionOperatorEnum::NOT_IN => $expressionBuilder->notIn($path, $values),
            default => throw new OperatorNotSupportedException($operator, $expressionBuilder::class),
        };
    }

    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @return TExpression
     */
    private function compileWithSingleValue(
        ExpressionBuilderInterface $expressionBuilder,
        string $operator,
        string $path,
        mixed $value,
    ): object {
        return match ($operator) {
            ExpressionOperatorEnum::EQUALS => $expressionBuilder->equals($path, $value),
            ExpressionOperatorEnum::NOT_EQUALS => $expressionBuilder->notEquals($path, $value),
            ExpressionOperatorEnum::CONTAINS => $expressionBuilder->contains($path, $value),
            ExpressionOperatorEnum::STARTS_WITH => $expressionBuilder->startsWith($path, $value),
            ExpressionOperatorEnum::ENDS_WITH => $expressionBuilder->endsWith($path, $value),
            ExpressionOperatorEnum::GREATER_THAN => $expressionBuilder->greaterThan($path, $value),
            ExpressionOperatorEnum::GREATER_THAN_OR_EQUAL => $expressionBuilder->greaterThanOrEqual($path, $value),
            ExpressionOperatorEnum::LESS_THAN => $expressionBuilder->lessThan($path, $value),
            ExpressionOperatorEnum::LESS_THAN_OR_EQUAL => $expressionBuilder->lessThanOrEqual($path, $value),
            default => throw new OperatorNotSupportedException($operator, $expressionBuilder::class),
        };
    }

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<object> $expressionBuilder
     */
    private function assertOperatorIsSupported(ExpressionBuilderInterface $expressionBuilder, string $operator): void
    {
        $this->expressionOperatorEnum->validateCase($operator);

        if ($expressionBuilder->supportsOperator($operator) === false) {
            throw new OperatorNotSupportedException($operator, $expressionBuilder::class);
        }
    }

    private function assertValueMatchesArity(string $operator, mixed $value): void
    {
        match ($this->expressionOperatorEnum->getValueArity($operator)) {
            ExpressionValueArityEnum::NONE => Assert::null($value, sprintf('Operator "%s" compares nothing, so it takes no value.', $operator)),
            ExpressionValueArityEnum::LIST => Assert::isList($value, sprintf('Operator "%s" compares a list of values.', $operator)),
            ExpressionValueArityEnum::RANGE => $this->assertRange($operator, $value),
            default => Assert::notNull($value, sprintf('Operator "%s" compares a single value.', $operator)),
        };
    }

    private function assertRange(string $operator, mixed $value): void
    {
        $message = sprintf('Operator "%s" compares a pair of bounds given as a list of exactly two values.', $operator);

        Assert::isList($value, $message);
        Assert::count($value, 2, $message);
    }
}
