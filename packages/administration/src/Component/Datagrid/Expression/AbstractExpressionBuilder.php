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
use Webmozart\Assert\Assert;

/**
 * The common part of every builder: it compiles a condition tree into the expression of its medium, so that
 * the tree is turned into DQL, a callback or a search clause by the same walk and the shape of every value is
 * validated in one place. A medium only says how it expresses each operation and how it composes expressions.
 *
 * The semantics are the same for every medium: a record whose value is missing never matches a comparison, but
 * always matches its negation — `notEquals`, `notIn` and `not()` — which is what the administrator asks for by
 * "is not".
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
     *
     * @param string $path Dot notation path, the same one datagrid fields use — `email`, `customerUser.email`
     * @param string $operator One of \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum
     * @param mixed $value Already validated against the arity of the operator — null, a single value, a list, or a pair of bounds `[from, to]`
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException When the medium does not express the operation
     * @return TExpression
     */
    abstract protected function compare(string $path, string $operator, mixed $value): object;

    /**
     * Negates a whole expression, which over a path leading through a to-many association means something
     * else than the negating operation of that expression, and both are legitimate questions:
     *
     *     not(compare('items.name', EQUALS, 'X'))  — no related row is named X
     *     compare('items.name', NOT_EQUALS, 'X')   — some related row is not named X
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
     * Refuses an expression a medium-specific condition built for another medium.
     *
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\ExpressionFromForeignMediumException
     * @return TExpression
     */
    abstract protected function assertExpression(object $expression): object;

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
            $condition instanceof MediumSpecificConditionInterface => $this->assertExpression($condition->compile($this)),
            default => throw new ConditionNotSupportedException($condition::class, static::class),
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

        $this->expressionOperatorEnum->validateCase($operator);
        $this->assertValueMatchesArity($operator, $comparison->value);

        return $this->compare($comparison->path, $operator, $comparison->value);
    }

    protected function assertValueMatchesArity(string $operator, mixed $value): void
    {
        match ($this->expressionOperatorEnum->getValueArity($operator)) {
            ExpressionValueArityEnum::NONE => Assert::null($value, sprintf('Operator "%s" compares nothing, so it takes no value.', $operator)),
            ExpressionValueArityEnum::LIST => $this->assertList($operator, $value),
            ExpressionValueArityEnum::RANGE => $this->assertRange($operator, $value),
            default => $this->assertSingleValue($operator, $value),
        };
    }

    protected function assertSingleValue(string $operator, mixed $value): void
    {
        $message = sprintf('Operator "%s" compares a single value.', $operator);

        Assert::notNull($value, $message);
        Assert::false(is_array($value), $message);
    }

    /**
     * A missing value is tested by `isNull`, a list holding it would match it in one medium and nothing in another.
     */
    protected function assertList(string $operator, mixed $value): void
    {
        Assert::isList($value, sprintf('Operator "%s" compares a list of values.', $operator));
        Assert::false(in_array(null, $value, true), sprintf('Operator "%s" compares a list of values, test a missing value by "isNull" instead.', $operator));
        $this->assertNoList($operator, $value);
    }

    /**
     * A missing bound would match nothing in one medium and fail in another, so a range is always complete.
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
