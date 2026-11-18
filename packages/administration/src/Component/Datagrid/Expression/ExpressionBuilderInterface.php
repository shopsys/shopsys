<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * Compiles a condition tree into an expression of one data source — DQL for a Doctrine query, a callback deciding
 * a row of data already in memory.
 *
 * The expression type is up to the implementation, which narrows the `object` return type to its own.
 * A condition the implementation cannot express fails instead of being ignored.
 * Implement it by extending `AbstractExpressionBuilder`.
 *
 * @template TExpression of object
 */
interface ExpressionBuilderInterface
{
    /**
     * @return TExpression
     */
    public function compile(ConditionInterface $condition): object;
}
