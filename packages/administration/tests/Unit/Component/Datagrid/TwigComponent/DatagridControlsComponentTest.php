<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\TwigComponent;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\DatagridConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridSearchInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\AdministrationBundle\Component\Datagrid\TwigComponent\DatagridControlsComponent;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridControlsComponentTest extends TestCase
{
    public function testQuickSearchTabIsOpenWhenNothingIsSearchedFor(): void
    {
        $component = $this->createComponent([new QuickSearch('Product_search', null, []), $this->createFilterPanel(false)]);

        $this->assertFalse($component->isFilterTabOpen());
    }

    public function testQuickSearchTabIsOpenWhenTheQuickSearchApplies(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', []);

        $component = $this->createComponent([$quickSearch, $this->createFilterPanel(false)], $quickSearch);

        $this->assertFalse($component->isFilterTabOpen());
    }

    /**
     * A composed filter outranks the quick search, so its tab is the one open.
     */
    public function testFilterTabIsOpenWhenTheFilterApplies(): void
    {
        $filterPanel = $this->createFilterPanel(true);

        $component = $this->createComponent([new QuickSearch('Product_search', 'hrnek', []), $filterPanel], $filterPanel);

        $this->assertTrue($component->isFilterTabOpen());
    }

    /**
     * The tab follows the search that applies, so it stays right when a project ranks the searches differently.
     */
    public function testQuickSearchTabIsOpenWhenItOutranksTheComposedFilter(): void
    {
        $quickSearch = new QuickSearch('Product_search', 'hrnek', []);

        $component = $this->createComponent([$quickSearch, $this->createFilterPanel(true)], $quickSearch);

        $this->assertFalse($component->isFilterTabOpen());
    }

    public function testFilterTabIsOpenWithoutQuickSearch(): void
    {
        $component = $this->createComponent([$this->createFilterPanel(false)]);

        $this->assertNull($component->getQuickSearch());
        $this->assertTrue($component->isFilterTabOpen());
    }

    /**
     * The domain control displays nothing without a choice, the domain condition applies anyway.
     */
    public function testDomainChoiceIsOfferedOnlyWithSeveralDomains(): void
    {
        $this->assertTrue($this->createComponent([$this->createScope([1, 2])])->hasDomainChoice());
        $this->assertFalse($this->createComponent([$this->createScope([1])])->hasDomainChoice());
        $this->assertFalse($this->createComponent([])->hasDomainChoice());
    }

    /**
     * @param object[] $parts
     */
    private function createComponent(
        array $parts,
        ?DatagridSearchInterface $appliedSearch = null,
    ): DatagridControlsComponent {
        $partsByClass = [];

        foreach ($parts as $part) {
            $partsByClass[$part::class] = $part;
        }

        $component = new DatagridControlsComponent();
        $component->state = new DatagridState(
            new DatagridConfig('Product', new CapturingAdapter($this->createStub(DataSourceInterface::class)), [], new FilterCollection()),
            $partsByClass,
            appliedSearch: $appliedSearch,
        );

        return $component;
    }

    private function createFilterPanel(bool $hasRules): FilterPanel
    {
        $data = $hasRules ? FilterFormData::createStartingPoint() : new FilterFormData();
        $formStub = $this->createStub(FormInterface::class);
        $formStub->method('isSubmitted')->willReturn(true);
        $formStub->method('getData')->willReturn($data);

        return new FilterPanel($formStub, new FilterCollection());
    }

    /**
     * @param int[] $domainIds
     */
    private function createScope(array $domainIds): DomainControlScope
    {
        return new DomainControlScope(DomainControlType::FILTER, $domainIds, null, 'crud_test', 'domainId');
    }
}
