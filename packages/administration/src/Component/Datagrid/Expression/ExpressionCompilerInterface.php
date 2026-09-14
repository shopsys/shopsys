<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Expression;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * Compiles a condition tree into the expression of one medium, using the builder of that medium.
 *
 * Every adapter calls it while building its data source, so the tree is turned into DQL, a callback or a
 * search clause by the same walk, and the shape of every value is validated in one place. A project adding
 * conditions of its own decorates the default implementation: it compiles its own nodes and hands the rest over.
 */
interface ExpressionCompilerInterface
{
    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\OperatorNotSupportedException
     * @return TExpression
     */
    public function compile(ExpressionBuilderInterface $expressionBuilder, ConditionInterface $condition): object;
}
