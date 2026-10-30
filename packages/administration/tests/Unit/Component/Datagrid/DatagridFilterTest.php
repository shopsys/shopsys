<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use LogicException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

/**
 * The rules of the filter, its precedence over the quick search included, are covered by `FilterSetResolverTest`,
 * `FilterPanelTest` and `DatagridStateFactoryTest`; this asserts that the datagrid sends the composed filter
 * with the request and offers no filter that could tell what a field hidden from the administrator holds.
 */
final class DatagridFilterTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);
    }

    public function testDatagridWithoutFiltersHasNoFilterPanel(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter);

        $state = $this->createState($datagrid, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]);
        $datagrid->createView($state);

        $this->assertNull($state->get(FilterPanel::class));
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testComposedRulesNarrowTheDatagrid(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter);
        $datagrid->filters()
            ->add(TextFilter::new('name'))
            ->add(NumericFilter::new('price'));

        $state = $this->createState($datagrid, ['operator' => 'and', 'groups' => [
            ['operator' => 'or', 'rules' => [
                ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                ['filter' => 'price', 'operator' => 'greaterThan', 'value' => '100'],
            ]],
        ]]);
        $datagrid->createView($state);

        $this->assertTrue($state->isNarrowedByAdministrator());
        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100.0)),
            $adapter->getLastRequest()->condition,
        );
    }

    public function testRestrictedFieldGetsNoFilterWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('name', ['filterable' => true])
            ->add('purchasePrice', ['filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $this->assertSame(['name'], $this->getOfferedFilterNames($this->createState($datagrid, [])));
    }

    /**
     * A declared filter replaces the one picked for the field of its name, so it has to be left out with the field —
     * and so does a filter declared over a property of the field under another name.
     */
    public function testFilterDeclaredForRestrictedFieldIsNotOfferedWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('name', ['filterable' => true])
            ->add('purchasePrice', ['filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);
        $datagrid->filters()
            ->add(NumericFilter::new('purchasePrice'))
            ->add(NumericFilter::new('purchasePrice')->setName('cost'));

        $state = $this->createState($datagrid, [
            'groups' => [['rules' => [['filter' => 'purchasePrice', 'operator' => 'greaterThan', 'value' => '100']]]],
        ]);
        $datagrid->createView($state);

        $this->assertSame(['name'], $this->getOfferedFilterNames($state));
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    /**
     * A filter over a path under a property of a hidden field, or over the association the property lies under,
     * compares what the field holds as well — only a filter over another branch of the association is offered.
     */
    public function testFilterOverPathOverlappingRestrictedFieldIsNotOfferedWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('customerUser', ['role' => 'ROLE_CUSTOMERS'])
            ->add('street', ['property' => 'address.street', 'role' => 'ROLE_CUSTOMERS']);
        $datagrid->filters()
            ->add(TextFilter::new('customerUser.email'))
            ->add(TextFilter::new('address'))
            ->add(TextFilter::new('address.city'))
            ->add(TextFilter::new('customerUserGroup'));

        $this->assertEqualsCanonicalizing(['address_city', 'customerUserGroup'], $this->getOfferedFilterNames($this->createState($datagrid, [])));
    }

    /**
     * A filter declared once the configuration is frozen would never be offered, so the declaration is refused.
     */
    public function testFiltersCannotBeDeclaredOnceTheConfigurationIsFrozen(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter);
        $datagrid->getConfig();

        $this->expectException(LogicException::class);

        $datagrid->filters();
    }

    /**
     * @return string[]
     */
    private function getOfferedFilterNames(DatagridState $state): array
    {
        $filterPanel = $state->get(FilterPanel::class);
        $this->assertNotNull($filterPanel);

        return array_keys(iterator_to_array($filterPanel->filters));
    }

    /**
     * @param array<string, mixed> $filterQuery
     */
    private function createState(Datagrid $datagrid, array $filterQuery): DatagridState
    {
        return DatagridStateFactoryFixture::createState($datagrid, DatagridStateFactoryFixture::createRequest(null, $filterQuery));
    }

    private function createDatagrid(CapturingAdapter $adapter, bool $hasPermission = true): Datagrid
    {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        $accessCheckerStub = $this->createStub(AccessCheckerInterface::class);
        $accessCheckerStub->method('hasPermission')->willReturn($hasPermission);

        return new Datagrid($adapter, $gridFactoryStub, $accessCheckerStub, $this->createStub(AdministratorGridFacade::class), $this->createStub(Security::class), [
            'name' => DatagridStateFactoryFixture::GRID_ID,
            'roleConstant' => 'ROLE_CRUD_TEST',
        ]);
    }
}
