<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * The negation of a whole condition, which over a path leading through a to-many association means
 * something else than the negating operation of that condition — see `ExpressionBuilderInterface::not()`.
 */
final readonly class Negation implements ConditionInterface
{
    public function __construct(
        public ConditionInterface $condition,
    ) {
    }
}
