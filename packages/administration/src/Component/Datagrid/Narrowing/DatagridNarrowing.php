<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;

/**
 * Everything that narrows the records of one datagrid on behalf of the administrator in the current request —
 * the domain control, the quick search and the filter panel — and the single condition they combine into.
 *
 * Resolved once by `DatagridNarrowingResolver` from the fields, the filters and the request state of the datagrid,
 * see `Datagrid::getNarrowing()`.
 */
final readonly class DatagridNarrowing
{
    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch|null $quickSearch Null when no field is searchable or the datagrid is built outside a request
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel|null $filterPanel Null when no filter is offered or the datagrid is built outside a request
     * @param bool $filterActive Whether the administrator composed any filter rule — the filter then outranks the quick search and its tab is the one open
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null $condition Everything above combined by AND, null lists every record
     */
    public function __construct(
        public ?DomainControlScope $domainControlScope = null,
        public ?QuickSearch $quickSearch = null,
        public ?FilterPanel $filterPanel = null,
        public bool $filterActive = false,
        public ?ConditionInterface $condition = null,
    ) {
    }
}
