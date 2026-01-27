<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

class AdvancedSearchFilterRegistry
{
    /**
     * @var array<string,array<string,\Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface>>
     */
    protected array $filtersByEntity = [];

    /**
     * @param iterable<\Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface> $filters
     */
    public function __construct(
        #[TaggedIterator('shopsys.advanced_search_filter')]
        iterable $filters,
    ) {
        foreach ($filters as $filter) {
            $this->filtersByEntity[$filter::getEntityType()][$filter->getName()] = $filter;
        }
    }

    /**
     * @return array
     */
    public function getAllFilters(): array
    {
        $flattenedFilters = [];

        foreach ($this->filtersByEntity as $filters) {
            foreach ($filters as $filter) {
                if (!in_array($filters, $flattenedFilters, true)) {
                    $flattenedFilters[] = $filter;
                }
            }
        }

        return $flattenedFilters;
    }

    /**
     * @param string $entityType
     * @return array<string,\Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface>
     */
    public function getFiltersForEntity(string $entityType): array
    {
        return $this->filtersByEntity[$entityType] ?? [];
    }

    /**
     * @param string $entityType
     * @param string $name
     * @return \Shopsys\FrameworkBundle\Model\AdvancedSearch\Filter\AdvancedSearchFilterInterface
     */
    public function getFilter(string $entityType, string $name): AdvancedSearchFilterInterface
    {
        return $this->filtersByEntity[$entityType][$name]
            ?? throw new InvalidArgumentException("Filter '{$name}' not found for entity '{$entityType}'. Is the entity filter namespace defined in services.yaml?");
    }
}
