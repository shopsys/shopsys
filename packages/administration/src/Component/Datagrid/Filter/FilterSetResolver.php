<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Datagrid\Filter;

use Collator;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\FrameworkBundle\Component\Localization\DisplayTimeZoneProviderInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorLocalizationFacade;

/**
 * Makes the filters offered to the administrator from the filters of a datagrid:
 *
 * - replaces each `GuessedFilter` by the filter `FilterGuesser` picks for its field;
 * - copies every other filter, so the declared one stays untouched and can serve several datagrids;
 * - sorts the filters by their labels;
 * - resolves each of them against the adapter (see `FilterInterface::resolveFor()`).
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
