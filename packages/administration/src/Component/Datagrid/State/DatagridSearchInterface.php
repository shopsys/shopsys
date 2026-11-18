<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * A part of the state of a datagrid by which the administrator asks which records to list, such as the quick search
 * or the filter. The condition of only one of the active searches applies, see `DatagridStateFactory::pickSearch()`.
 */
interface DatagridSearchInterface
{
    /**
     * Whether the administrator asked anything. An active search outranks those after it even when its condition
     * is null.
     */
    public function isActive(): bool;

    /**
     * Null when the part narrows nothing.
     */
    public function createCondition(): ?ConditionInterface;
}
