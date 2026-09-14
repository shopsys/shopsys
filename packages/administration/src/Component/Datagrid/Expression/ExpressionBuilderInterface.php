<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * Compiles the condition narrowing a datagrid into the expression of one medium — DQL for a Doctrine query,
 * a callback for data already in memory, a query clause for a search engine.
 *
 * The builder is bound to the data set it builds for and may prepare it for the expression, which is what
 * makes a path leading through an association work: `customerUser.email` adds the join it needs. The concrete
 * expression type is up to the medium (an implementation narrows the `object` return type to its own), and the
 * expression is only ever handed back to the adapter that compiled it.
 *
 * Extend `AbstractExpressionBuilder`, which walks the condition tree and validates every value once for every
 * medium, so that a medium only says how it expresses each operation of `ExpressionOperatorEnum`. What a medium
 * cannot say through the operations, it says through a condition of its own (see
 * `MediumSpecificConditionInterface` and `DqlCondition`).
 *
 * @phpstan-type ScalarValue string|int|float|bool|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money
 * @phpstan-type ComparableValue string|int|float|\DateTimeInterface|\Shopsys\FrameworkBundle\Component\Money\Money
 * @template TExpression of object
 */
interface ExpressionBuilderInterface
{
    /**
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException
     * @return TExpression
     */
    public function compile(ConditionInterface $condition): object;
}
