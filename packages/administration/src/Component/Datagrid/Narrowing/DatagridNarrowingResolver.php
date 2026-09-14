<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterSetResolver;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;

/**
 * Resolves what narrows one datagrid in the current request: validates the searchable fields and the filters against
 * the adapter, builds the quick search and the filter panel from the request state, and combines their conditions
 * with the one of the domain control.
 *
 * Replace the service in a project to narrow differently, for example to apply the quick search and the filter together.
 */
class DatagridNarrowingResolver
{
    public function __construct(
        protected readonly SearchablePathResolver $searchablePathResolver,
        protected readonly FilterSetResolver $filterSetResolver,
        protected readonly DatagridFormFactory $datagridFormFactory,
    ) {
    }

    /**
     * The declarations are validated even outside a request, so that one that cannot work fails in a test or
     * a command as well; only the forms need the request.
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields The fields the administrator is allowed to see
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection $declaredFilters The declared filters the administrator is allowed to use
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function resolve(
        AdapterInterface $adapter,
        array $fields,
        FilterCollection $declaredFilters,
        DatagridRequestState $requestState,
        ?DomainControlScope $domainControlScope,
    ): DatagridNarrowing {
        $labelsByPath = $this->searchablePathResolver->resolve($adapter, $fields);
        $filters = $this->filterSetResolver->resolve($adapter, $declaredFilters);

        $quickSearch = $labelsByPath !== [] && $requestState->inRequest ? $this->createQuickSearch($labelsByPath, $requestState) : null;
        $filterPanel = $filters->isEmpty() === false && $requestState->inRequest ? $this->createFilterPanel($filters, $requestState) : null;

        return new DatagridNarrowing(
            $domainControlScope,
            $quickSearch,
            $filterPanel,
            $filterPanel?->hasRules() === true,
            $this->combine($domainControlScope?->createCondition(), $this->createSearchCondition($quickSearch, $filterPanel)),
        );
    }

    /**
     * What the administrator searched for, null when nothing. The filter and the quick search are two ways of
     * asking the same question, so only one of them applies — the filter whenever any rule was composed (an invalid
     * one narrows nothing and shows its errors), the quick search otherwise.
     */
    protected function createSearchCondition(?QuickSearch $quickSearch, ?FilterPanel $filterPanel): ?ConditionInterface
    {
        if ($filterPanel !== null && $filterPanel->hasRules()) {
            return $filterPanel->createCondition();
        }

        return $quickSearch?->createCondition();
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface|null The given conditions combined by AND, null when there is none
     */
    protected function combine(?ConditionInterface ...$conditions): ?ConditionInterface
    {
        $conditions = array_values(array_filter($conditions));

        return match (count($conditions)) {
            0 => null,
            1 => $conditions[0],
            default => Condition::andX(...$conditions),
        };
    }

    /**
     * @param array<string, string> $labelsByPath
     */
    protected function createQuickSearch(array $labelsByPath, DatagridRequestState $requestState): QuickSearch
    {
        $form = $this->datagridFormFactory->createQuickSearchForm($requestState);

        return new QuickSearch($form, $this->datagridFormFactory->getSearchTerm($form), $labelsByPath);
    }

    protected function createFilterPanel(FilterCollection $filters, DatagridRequestState $requestState): FilterPanel
    {
        return new FilterPanel(
            $this->datagridFormFactory->createFilterForm($filters, $requestState),
            $this->datagridFormFactory->createFilterPrototypesForm($filters, $requestState),
            $filters,
        );
    }
}
