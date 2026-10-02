<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use LogicException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The rules of the filter, its precedence over the quick search included, are covered by `FilterSetResolverTest`,
 * `FilterPanelTest` and `DatagridNarrowingResolverTest`; this asserts that the datagrid sends the composed filter
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
        $datagrid = $this->createDatagrid($adapter, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]);

        $datagrid->createView();

        $this->assertNull($datagrid->getNarrowing()->filterPanel);
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testComposedRulesNarrowTheDatagrid(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, ['operator' => 'and', 'groups' => [
            ['operator' => 'or', 'rules' => [
                ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                ['filter' => 'price', 'operator' => 'greaterThan', 'value' => '100'],
            ]],
        ]]);
        $datagrid->filters()
            ->add(TextFilter::new('name'))
            ->add(NumericFilter::new('price'));

        $datagrid->createView();

        $this->assertTrue($datagrid->getNarrowing()->filterActive);
        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100.0)),
            $adapter->getLastRequest()->condition,
        );
    }

    public function testRestrictedFieldGetsNoFilterWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, [], false);
        $datagrid
            ->add('name', ['filterable' => true])
            ->add('purchasePrice', ['filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $this->assertSame(['name'], $this->getOfferedFilterNames($datagrid));
    }

    /**
     * A declared filter replaces the one picked for the field of its name, so it has to be left out with the field —
     * and so does a filter declared over a property of the field under another name.
     */
    public function testFilterDeclaredForRestrictedFieldIsNotOfferedWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, [
            'groups' => [['rules' => [['filter' => 'purchasePrice', 'operator' => 'greaterThan', 'value' => '100']]]],
        ], false);
        $datagrid
            ->add('name', ['filterable' => true])
            ->add('purchasePrice', ['filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);
        $datagrid->filters()
            ->add(NumericFilter::new('purchasePrice'))
            ->add(NumericFilter::new('purchasePrice')->setName('cost'));

        $datagrid->createView();

        $this->assertSame(['name'], $this->getOfferedFilterNames($datagrid));
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    /**
     * A filter over a path under a property of a hidden field, or over the association the property lies under,
     * compares what the field holds as well — only a filter over another branch of the association is offered.
     */
    public function testFilterOverPathOverlappingRestrictedFieldIsNotOfferedWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, [], false);
        $datagrid
            ->add('customerUser', ['role' => 'ROLE_CUSTOMERS'])
            ->add('street', ['property' => 'address.street', 'role' => 'ROLE_CUSTOMERS']);
        $datagrid->filters()
            ->add(TextFilter::new('customerUser.email'))
            ->add(TextFilter::new('address'))
            ->add(TextFilter::new('address.city'))
            ->add(TextFilter::new('customerUserGroup'));

        $this->assertEqualsCanonicalizing(['address_city', 'customerUserGroup'], $this->getOfferedFilterNames($datagrid));
    }

    /**
     * A filter declared once the narrowing is resolved would never be offered, so the declaration is refused.
     */
    public function testFiltersCannotBeDeclaredOnceTheNarrowingIsResolved(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, []);
        $datagrid->getNarrowing();

        $this->expectException(LogicException::class);

        $datagrid->filters();
    }

    /**
     * @return string[]
     */
    private function getOfferedFilterNames(Datagrid $datagrid): array
    {
        $filterPanel = $datagrid->getNarrowing()->filterPanel;
        $this->assertNotNull($filterPanel);

        return array_keys(iterator_to_array($filterPanel->filters));
    }

    /**
     * @param array<string, mixed> $filterQuery
     */
    private function createDatagrid(
        CapturingAdapter $adapter,
        array $filterQuery,
        bool $hasPermission = true,
    ): Datagrid {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        $accessCheckerStub = $this->createStub(AccessCheckerInterface::class);
        $accessCheckerStub->method('hasPermission')->willReturn($hasPermission);

        return new Datagrid(
            $adapter,
            $gridFactoryStub,
            $accessCheckerStub,
            $this->createStub(AdministratorGridFacade::class),
            $this->createStub(Security::class),
            DatagridNarrowingResolverFactory::create(),
            DatagridNarrowingResolverFactory::createRequestState(null, $filterQuery),
            null,
            [
                'name' => DatagridNarrowingResolverFactory::GRID_ID,
                'roleConstant' => 'ROLE_CRUD_TEST',
            ],
        );
    }
}
