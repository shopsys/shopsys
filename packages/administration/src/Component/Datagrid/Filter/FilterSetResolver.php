<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Collator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;

/**
 * Makes the filters offered to the administrator — those picked for the fields declared `filterable` merged with
 * the declared ones — and adapts them to the medium of the datagrid.
 *
 * The declared filters stay untouched: the offered ones are their copies, so one declaration can serve several
 * datagrids over different media.
 */
class FilterSetResolver
{
    public function __construct(
        protected readonly FilterGuesser $filterGuesser,
        protected readonly ExpressionOperatorEnum $expressionOperatorEnum,
        protected readonly DisplayTimeZoneProviderInterface $displayTimeZoneProvider,
        protected readonly AdministratorLocalizationFacade $administratorLocalizationFacade,
    ) {
    }

    public function resolve(AdapterInterface $adapter, FilterCollection $filters): FilterCollection
    {
        $environment = new FilterEnvironment(
            $adapter,
            $this->expressionOperatorEnum,
            $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin(),
        );
        $resolvedFilters = new FilterCollection();

        foreach ($this->sort($this->collect($filters, $environment)) as $filter) {
            $filter->resolveFor($environment);
            $resolvedFilters->add($filter);
        }

        return $resolvedFilters;
    }

    /**
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface[]
     */
    protected function collect(FilterCollection $filters, FilterEnvironment $environment): array
    {
        $collectedFilters = [];

        foreach ($filters as $filter) {
            $collectedFilters[] = $filter instanceof GuessedFilter
                ? $this->filterGuesser->guess($filter->field, $environment)
                : clone $filter;
        }

        return $collectedFilters;
    }

    /**
     * Sorts the filters alphabetically by their labels in the language of the administration:
     *
     * - the administrator looks a filter up by its name;
     * - the order does not depend on where the field or the filter was declared (a controller or an extension).
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface[] $filters
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface[]
     */
    protected function sort(array $filters): array
    {
        if ($filters === []) {
            return [];
        }

        $collator = new Collator($this->administratorLocalizationFacade->getCurrentAdminLocaleOrDefault());
        usort($filters, static fn (FilterInterface $first, FilterInterface $second): int => (int)$collator->compare($first->getLabel(), $second->getLabel()));

        return $filters;
    }
}
