<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Search;

use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Search\Filter;
use Shopsys\AdministrationBundle\Component\Search\SearchConfig;

final class SearchConfigTest extends TestCase
{
    public function testQuickSearchIsDisabledByDefault(): void
    {
        $searchConfig = new SearchConfig();

        $this->assertFalse($searchConfig->isQuickSearchEnabled());
        $this->assertNull($searchConfig->getQuickSearchDefinition());
    }

    public function testQuickSearchConfigurationIsStored(): void
    {
        $searchConfig = new SearchConfig();

        $searchConfig->enableQuickSearch(
            fields: ['name', 'brand.name'],
            placeholder: 'Search by name…',
            infoMessage: 'Searches name and brand name.',
        );

        $this->assertTrue($searchConfig->isQuickSearchEnabled());
        $quickSearchDefinition = $searchConfig->getQuickSearchDefinition();
        $this->assertSame(['name', 'brand.name'], $quickSearchDefinition->getFields());
        $this->assertSame('Search by name…', $quickSearchDefinition->getPlaceholder());
        $this->assertSame('Searches name and brand name.', $quickSearchDefinition->getInfoMessage());
        $this->assertNull($quickSearchDefinition->getQueryCallback());
    }

    public function testQueryCallbackIsStored(): void
    {
        $searchConfig = new SearchConfig();
        $queryCallback = static function (QueryBuilder $queryBuilder, string $searchText): void {
        };

        $quickSearchDefinition = $searchConfig->enableQuickSearch()->queryCallback($queryCallback);

        $this->assertSame($queryCallback, $quickSearchDefinition->getQueryCallback());
    }

    public function testAdvancedSearchIsDisabledWithoutFilters(): void
    {
        $searchConfig = new SearchConfig();

        $this->assertFalse($searchConfig->hasAdvancedSearch());
        $this->assertNull($searchConfig->getDefaultFilterName());
    }

    public function testFiltersCanBeAddedAndRemoved(): void
    {
        $searchConfig = new SearchConfig();
        $nameFilter = Filter::create('name', 'Name');

        $searchConfig
            ->addFilter($nameFilter)
            ->addFilter(Filter::create('city', 'City'));

        $this->assertTrue($searchConfig->hasAdvancedSearch());
        $this->assertSame(['name', 'city'], array_keys($searchConfig->getFilters()));
        $this->assertSame($nameFilter, $searchConfig->getFilter('name'));

        $searchConfig->removeFilter('name');

        $this->assertSame(['city'], array_keys($searchConfig->getFilters()));
        $this->assertNull($searchConfig->getFilter('name'));
    }

    public function testDefaultFilterFallsBackToFirstAddedFilter(): void
    {
        $searchConfig = new SearchConfig();
        $searchConfig
            ->addFilter(Filter::create('name', 'Name'))
            ->addFilter(Filter::create('city', 'City'));

        $this->assertSame('name', $searchConfig->getDefaultFilterName());

        $searchConfig->setDefaultFilter('city');
        $this->assertSame('city', $searchConfig->getDefaultFilterName());

        $searchConfig->removeFilter('city');
        $this->assertSame('name', $searchConfig->getDefaultFilterName());
    }
}
