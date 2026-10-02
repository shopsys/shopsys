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

    /**
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FilterNotApplicableException
     */
    public function resolve(
        AdapterInterface $adapter,
        array $fields,
        FilterCollection $declaredFilters,
    ): FilterCollection {
        $environment = new FilterEnvironment(
            $adapter,
            $this->expressionOperatorEnum,
            $this->displayTimeZoneProvider->getDisplayTimeZoneForAdmin(),
        );
        $filters = new FilterCollection();

        foreach ($this->sort($this->collect($fields, $declaredFilters, $environment)) as $filter) {
            $filter->resolveFor($environment);
            $filters->add($filter);
        }

        return $filters;
    }

    /**
     * A filter declared under the name of a field replaces the picked one, so a field is only guessed for when
     * nothing was declared for it — and a field whose filter cannot be guessed can still be declared explicitly.
     *
     * @param \Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor[] $fields
     * @throws \Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotFilterableException
     * @return \Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterInterface[]
     */
    protected function collect(array $fields, FilterCollection $declaredFilters, FilterEnvironment $environment): array
    {
        $filtersByName = [];

        foreach ($fields as $field) {
            if ($field->isFilterable() === false) {
                continue;
            }

            $filterName = $field->getFilterName();
            $filtersByName[$filterName] = $declaredFilters->has($filterName)
                ? clone $declaredFilters->get($filterName)
                : $this->filterGuesser->guess($field, $environment);
        }

        foreach ($declaredFilters as $filter) {
            $filtersByName[$filter->getName()] ??= clone $filter;
        }

        return array_values($filtersByName);
    }

    /**
     * The alphabetical order of the labels in the language of the administration — the administrator looks a filter
     * up by its name, and the order then does not depend on where the field or the filter was declared (a controller
     * or an extension).
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
