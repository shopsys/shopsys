<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;

/**
 * Everything that narrows the records of one datagrid on behalf of the administrator in the current request —
 * the domain control and the quick search — and the single condition they combine into.
 *
 * Resolved once by `DatagridNarrowingResolver` from the fields and the request state of the datagrid, see
 * `Datagrid::getNarrowing()`.
 */
final readonly class DatagridNarrowing
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch|null $quickSearch Null when no field is searchable or the datagrid is built outside a request
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition Everything above combined by AND, null lists every record
     */
    public function __construct(
        public ?DomainControlScope $domainControlScope = null,
        public ?QuickSearch $quickSearch = null,
        public ?ConditionInterface $condition = null,
    ) {
    }
}
