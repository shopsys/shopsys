<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\State;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;

/**
 * A part of the state of a datagrid limiting what the datagrid lists at all — the domain control. Its condition
 * always applies, combined with the others by AND.
 */
interface DatagridScopeInterface
{
    /**
     * Null when the part limits nothing.
     */
    public function createCondition(): ?ConditionInterface;
}
