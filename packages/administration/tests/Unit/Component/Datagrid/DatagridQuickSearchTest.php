<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

/**
 * The rules of the quick search are covered by `SearchablePathResolverTest` and `DatagridNarrowingResolverTest`;
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
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
        $datagrid->add('name');

        $datagrid->createView();

        $this->assertNull($datagrid->getNarrowing()->quickSearch);
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testTermIsSearchedInEverySearchableField(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
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

        $datagrid->createView();

        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::contains('brand.name', 'hrnek')),
            $adapter->getLastRequest()->condition,
        );
        $this->assertSame(['Name', 'Brand'], $datagrid->getNarrowing()->quickSearch?->getLabels());
    }

    /**
     * The narrowing is resolved from the datagrid as configured at the first call, so that the condition sent to
     * the adapter and the forms rendered by the page are always the same ones.
     */
    public function testNarrowingIsResolvedOnce(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
        $datagrid->add('name', ['searchable' => true]);
        $narrowing = $datagrid->getNarrowing();

        $datagrid->update('name', ['searchable' => false]);
        $datagrid->createView();

        $this->assertSame($narrowing, $datagrid->getNarrowing());
        $this->assertEquals(Condition::orX(Condition::contains('name', 'hrnek')), $adapter->getLastRequest()->condition);
    }

    /**
     * The search could otherwise tell what a field the administrator is not allowed to see holds.
     */
    public function testRestrictedFieldIsNotSearchedInWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, 'hrnek', false);
        $datagrid
            ->add('name', ['searchable' => true])
            ->add('purchasePrice', ['searchable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $datagrid->createView();

        $this->assertEquals(Condition::orX(Condition::contains('name', 'hrnek')), $adapter->getLastRequest()->condition);
    }

    private function createDatagrid(CapturingAdapter $adapter, string $term, bool $hasPermission = true): Datagrid
    {
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
            DatagridNarrowingResolverFactory::create(),
            DatagridNarrowingResolverFactory::createRequestState(['text' => $term]),
            null,
            [
                'name' => DatagridNarrowingResolverFactory::GRID_ID,
                'roleConstant' => 'ROLE_CRUD_TEST',
            ],
        );
    }
}
