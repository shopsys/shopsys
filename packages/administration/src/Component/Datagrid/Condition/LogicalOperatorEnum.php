<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Condition;

enum LogicalOperatorEnum
{
    /**
     * Every condition has to match — no condition at all matches everything.
     */
    case AND;

    /**
     * Some condition has to match — no condition at all matches nothing.
     */
    case OR;
}
