<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
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
 * The rules of the quick search are covered by `SearchablePathResolverTest` and `DatagridStateFactoryTest`;
 * this asserts that the datagrid offers it over its current fields and sends it with the request.
 */
final class DatagridQuickSearchTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);

        Translator::injectSelf($translatorStub);
    }

    public function testDatagridWithoutSearchableFieldHasNoQuickSearch(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);
        $datagrid->add('name');

        $state = $this->createState($datagrid, 'hrnek');
        $datagrid->createView($state);

        $this->assertNull($state->get(QuickSearch::class));
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testTermIsSearchedInEverySearchableField(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter);
        $datagrid
            ->add('name', [
                'label' => 'Name',
                'searchable' => true,
            ])
            ->add('brandName', [
                'label' => 'Brand',
                'searchable' => true,
                'property' => 'brand.name',
            ])
            ->add('createdAt');

        $state = $this->createState($datagrid, 'hrnek');
        $datagrid->createView($state);

        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::contains('brand.name', 'hrnek')),
            $adapter->getLastRequest()->condition,
        );
        $this->assertSame(['Name', 'Brand'], $state->get(QuickSearch::class)?->getLabels());
    }

    /**
     * The search could otherwise tell what a field the administrator is not allowed to see holds.
     */
    public function testRestrictedFieldIsNotSearchedInWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('name', ['searchable' => true])
            ->add('purchasePrice', ['searchable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $datagrid->createView($this->createState($datagrid, 'hrnek'));

        $this->assertEquals(Condition::orX(Condition::contains('name', 'hrnek')), $adapter->getLastRequest()->condition);
    }

    private function createState(Datagrid $datagrid, string $term): DatagridState
    {
        return DatagridStateFactoryFixture::createState($datagrid, DatagridStateFactoryFixture::createRequest(['text' => $term]));
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
