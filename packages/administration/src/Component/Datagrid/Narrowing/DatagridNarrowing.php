<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;

/**
 * Everything that narrows the records of one datagrid on behalf of the administrator in the current request —
 * the domain control — and the single condition it narrows by.
 *
 * Resolved once by `DatagridNarrowingResolver`, see `Datagrid::getNarrowing()`.
 */
final readonly class DatagridNarrowing
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition The condition of the domain control, null lists every record
     */
    public function __construct(
        public ?DomainControlScope $domainControlScope = null,
        public ?ConditionInterface $condition = null,
    ) {
    }
}
