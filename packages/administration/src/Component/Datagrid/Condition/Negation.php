<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

/**
 * Over a path through a to-many association it differs from the negating operation, and both are legitimate
 * questions:
 *
 *     Condition::not(Condition::equals('items.name', 'X'))  — no item is named X
 *     Condition::notEquals('items.name', 'X')               — some item is not named X
 */
final readonly class Negation implements ConditionInterface
{
    public function __construct(
        public ConditionInterface $condition,
    ) {
    }
}
