<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Override;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Comparison;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Composite;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\LogicalOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Negation;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\UnsupportedConditionException;
use Webmozart\Assert\Assert;

/**
 * The common part of every builder:
 *
 * - it walks the condition tree;
 * - it checks the shape of every value against the arity of its operator;
 * - it leaves to the concrete builder how it expresses each operation (`compare()`) and how it composes expressions
 *   (`not()`, `andX()`, `orX()`).
 *
 * The meaning of the operations is described on `ExpressionOperatorEnum`.
 *
 * @template TExpression of object
 * @implements \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression>
 */
abstract class AbstractExpressionBuilder implements ExpressionBuilderInterface
{
    public function __construct(
        protected readonly ExpressionOperatorEnum $expressionOperatorEnum,
    ) {
    }

    /**
     * Expresses one operation of the vocabulary on one path.
     * `in` and `notIn` never get an empty list: an empty `in` matches nothing and is compiled into `orX()`, an empty
     * `notIn` matches everything and is compiled into `andX()`.
     *
     * To express an operation of a project, extend the concrete builder (`DqlExpressionBuilder`,
     * `PhpExpressionBuilder`), express the operation here and hand every other one to `parent::compare()`.
     *
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     * @param mixed $value Already validated against the arity of the operator — null, a single value, a list, or a pair of bounds `[from, to]`
     * @return TExpression
     */
    abstract protected function compare(string $path, string $operator, mixed $value): object;

    /**
     * Negates a whole expression, see `Negation`.
     *
     * @param TExpression $expression
     * @return TExpression
     */
    abstract protected function not(object $expression): object;

    /**
     * @param TExpression ...$expressions No expression at all matches everything
     * @return TExpression
     */
    abstract protected function andX(object ...$expressions): object;

    /**
     * @param TExpression ...$expressions No expression at all matches nothing
     * @return TExpression
     */
    abstract protected function orX(object ...$expressions): object;

    /**
     * {@inheritdoc}
     */
    #[Override]
    public function compile(ConditionInterface $condition): object
    {
        return match (true) {
            $condition instanceof Comparison => $this->compileComparison($condition),
            $condition instanceof Composite => $this->compileComposite($condition),
            $condition instanceof Negation => $this->not($this->compile($condition->condition)),
            default => throw UnsupportedConditionException::conditionNotSupported($condition::class, static::class),
        };
    }

    /**
     * @return TExpression
     */
    protected function compileComposite(Composite $composite): object
    {
        $expressions = array_map($this->compile(...), $composite->conditions);

        return match ($composite->operator) {
            LogicalOperatorEnum::AND => $this->andX(...$expressions),
            LogicalOperatorEnum::OR => $this->orX(...$expressions),
        };
    }

    /**
     * @return TExpression
     */
    protected function compileComparison(Comparison $comparison): object
    {
        $operator = $comparison->operator;

        $this->assertValueMatchesArity($operator, $comparison->value);

        return match (true) {
            $operator === ExpressionOperatorEnum::IN && $comparison->value === [] => $this->orX(),
            $operator === ExpressionOperatorEnum::NOT_IN && $comparison->value === [] => $this->andX(),
            default => $this->compare($comparison->path, $operator, $comparison->value),
        };
    }

    protected function assertValueMatchesArity(string $operator, mixed $value): void
    {
        match ($this->expressionOperatorEnum->getValueArity($operator)) {
            ExpressionValueArityEnum::NONE => Assert::null($value, sprintf('Operator "%s" compares nothing, so it takes no value.', $operator)),
            ExpressionValueArityEnum::LIST => $this->assertList($operator, $value),
            ExpressionValueArityEnum::RANGE => $this->assertRange($operator, $value),
            ExpressionValueArityEnum::SINGLE => $this->assertSingleValue($operator, $value),
        };
    }

    protected function assertSingleValue(string $operator, mixed $value): void
    {
        $message = sprintf('Operator "%s" compares a single value.', $operator);

        Assert::notNull($value, $message);
        Assert::false(is_array($value), $message);
    }

    /**
     * A list holding null fails, as a missing value matches no comparison.
     */
    protected function assertList(string $operator, mixed $value): void
    {
        Assert::isList($value, sprintf('Operator "%s" compares a list of values.', $operator));
        Assert::false(in_array(null, $value, true), sprintf('Operator "%s" compares a list of values, test a missing value by "isNull" instead.', $operator));
        $this->assertNoList($operator, $value);
    }

    /**
     * A range open on one side is `greaterThanOrEqual` or `lessThanOrEqual`.
     */
    protected function assertRange(string $operator, mixed $value): void
    {
        $message = sprintf('Operator "%s" compares a pair of bounds given as a list of exactly two values.', $operator);

        Assert::isList($value, $message);
        Assert::count($value, 2, $message);
        Assert::false(in_array(null, $value, true), $message);
        $this->assertNoList($operator, $value);
    }

    /**
     * @param list<mixed> $values
     */
    protected function assertNoList(string $operator, array $values): void
    {
        foreach ($values as $value) {
            Assert::false(is_array($value), sprintf('Operator "%s" compares single values, a list cannot be one of them.', $operator));
        }
    }
}
