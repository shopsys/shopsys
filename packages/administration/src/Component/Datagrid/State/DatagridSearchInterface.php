<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * A part of the state of a datagrid by which the administrator asks which records to list — the quick search or
 * the filter. They are several ways of asking the same question, so the condition of only one of the active ones
 * applies, see `DatagridStateFactory::getSearchPrecedence()`.
 */
interface DatagridSearchInterface
{
    /**
     * Whether the administrator asked anything — an active part outranks those after it even when its condition
     * is null (an invalid filter narrows nothing and shows its errors instead).
     */
    public function isActive(): bool;

    /**
     * Null when the part narrows nothing.
     */
    public function createCondition(): ?ConditionInterface;
}
