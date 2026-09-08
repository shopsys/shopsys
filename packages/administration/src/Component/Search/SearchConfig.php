<?php

declare(strict_types=1);

namespace Shopsys\AdministrationBundle\Component\Search;

final class SearchConfig
{
    public const string QUICK_SEARCH_QUERY_PARAMETER = 'q';

    public const string ADVANCED_SEARCH_RULES_QUERY_PARAMETER = 'f';

    public const string ADVANCED_SEARCH_FLAG_QUERY_PARAMETER = 'advancedSearch';

    private ?QuickSearchDefinition $quickSearchDefinition = null;

    /**
     * @var array<string, \Shopsys\AdministrationBundle\Component\Search\FilterInterface>
     */
    private array $filters = [];

    private ?string $defaultFilterName = null;

    /**
     * Enables the quick search box on the list page, searching the given fields with a case- and accent-insensitive match.
     *
     * @param string[] $fields Field paths in dot notation (e.g. "catnum", "items.catnum", "translations.name")
     */
    public function enableQuickSearch(
        array $fields = [],
        ?string $placeholder = null,
        ?string $infoMessage = null,
    ): QuickSearchDefinition {
        $this->quickSearchDefinition = new QuickSearchDefinition($fields, $placeholder, $infoMessage);

        return $this->quickSearchDefinition;
    }

    public function isQuickSearchEnabled(): bool
    {
        return $this->quickSearchDefinition !== null;
    }

    public function getQuickSearchDefinition(): ?QuickSearchDefinition
    {
        return $this->quickSearchDefinition;
    }

    /**
     * Adds an advanced search filter. At least one filter enables the Advanced search tab on the list page.
     * A filter with an already used name replaces the previous one.
     */
    public function addFilter(FilterInterface $filter): self
    {
        $this->filters[$filter->getName()] = $filter;

        return $this;
    }

    public function removeFilter(string $filterName): self
    {
        unset($this->filters[$filterName]);

        return $this;
    }

    /**
     * Sets the filter preselected in a newly added rule. Defaults to the first added filter.
     */
    public function setDefaultFilter(string $filterName): self
    {
        $this->defaultFilterName = $filterName;

        return $this;
    }

    public function hasAdvancedSearch(): bool
    {
        return $this->filters !== [];
    }

    /**
     * @return array<string, \Shopsys\AdministrationBundle\Component\Search\FilterInterface>
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    public function getFilter(string $filterName): ?FilterInterface
    {
        return $this->filters[$filterName] ?? null;
    }

    public function getDefaultFilterName(): ?string
    {
        if ($this->defaultFilterName !== null && array_key_exists($this->defaultFilterName, $this->filters)) {
            return $this->defaultFilterName;
        }

        return array_key_first($this->filters);
    }
}
