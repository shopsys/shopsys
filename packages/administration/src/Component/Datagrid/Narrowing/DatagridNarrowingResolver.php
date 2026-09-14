<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Narrowing;

use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\ConditionInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\SearchablePathResolver;

/**
 * Resolves what narrows one datagrid in the current request: validates the searchable fields against the adapter,
 * builds the quick search from the request state, and combines its condition with the one of the domain control.
 *
 * Replace the service in a project to narrow differently.
 */
class DatagridNarrowingResolver
{
    public function __construct(
        protected readonly SearchablePathResolver $searchablePathResolver,
        protected readonly DatagridFormFactory $datagridFormFactory,
    ) {
    }

    /**
     * The declarations are validated even outside a request, so that one that cannot work fails in a test or
     * a command as well; only the forms need the request.
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields The fields the administrator is allowed to see
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException
     */
    public function resolve(
        AdapterInterface $adapter,
        array $fields,
        DatagridRequestState $requestState,
        ?DomainControlScope $domainControlScope,
    ): DatagridNarrowing {
        $labelsByPath = $this->searchablePathResolver->resolve($adapter, $fields);

        $quickSearch = $labelsByPath !== [] && $requestState->inRequest ? $this->createQuickSearch($labelsByPath, $requestState) : null;

        return new DatagridNarrowing(
            $domainControlScope,
            $quickSearch,
            condition: $this->combine($domainControlScope?->createCondition(), $quickSearch?->createCondition()),
        );
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
}
