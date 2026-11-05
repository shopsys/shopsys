<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface;

/**
 * A condition the shared vocabulary cannot express, understood by one medium only — an aggregate or a
 * computed value said in DQL (`DqlCondition`), a native clause of a search engine.
 *
 * The condition compiles itself, because only it knows the builder it needs. It composes with the rest of
 * the tree freely, so a medium-specific rule sits in an `OR` group next to ordinary ones; the price is that
 * the datagrid works with that medium only, which the condition has to say clearly when given another one.
 */
interface MediumSpecificConditionInterface extends ConditionInterface
{
    /**
     * @template TExpression of object
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionBuilderInterface<TExpression> $expressionBuilder
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\ConditionNotSupportedException When the builder is of another medium
     * @return TExpression
     */
    public function compile(ExpressionBuilderInterface $expressionBuilder): object;
}
