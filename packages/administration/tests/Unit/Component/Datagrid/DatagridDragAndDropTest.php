<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\EntityClassAwareAdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\FrameworkBundle\Component\Grid\Column;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\OrderableDummy;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

/**
 * Saving the order renumbers the listed records, so reordering only the searched or filtered ones would mix up
 * the positions of the rest.
 */
final class DatagridDragAndDropTest extends TestCase
{
    private ?DataSourceInterface $gridDataSource = null;

    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    public function testDragAndDropIsEnabledWhenTheListIsNotNarrowed(): void
    {
        $gridMock = $this->createGridMock();
        $gridMock->expects($this->once())->method('enableDragAndDrop')->with(OrderableDummy::class, 'position');
        $datagrid = $this->createDatagrid($gridMock, $this->createStub(DataSourceInterface::class));

        $datagrid->createView($this->createState($datagrid));
    }

    /**
     * @param array<string, mixed>|null $quickSearchQuery
     * @param array<string, mixed>|null $filterQuery
     */
    #[DataProvider('provideNarrowingQueries')]
    public function testNarrowedRecordsAreListedByTheirPositionWithoutDragAndDrop(
        ?array $quickSearchQuery,
        ?array $filterQuery,
    ): void {
        $gridMock = $this->createGridMock();
        $gridMock->expects($this->never())->method('enableDragAndDrop');
        $datagrid = $this->createDatagrid($gridMock, $this->createDataSourceExpectingOrderByPosition());
        $datagrid->filters()->add(TextFilter::new('name'));

        $datagrid->createView($this->createState($datagrid, $quickSearchQuery, $filterQuery));
        $this->gridDataSource?->getPaginatedRows();
    }

    /**
     * @return iterable<string, array{array<string, mixed>|null, array<string, mixed>|null}>
     */
    public static function provideNarrowingQueries(): iterable
    {
        yield 'searched' => [['text' => 'hrnek'], null];

        yield 'filtered' => [null, ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek']]]]]];
    }

    private function createGridMock(): Grid&MockObject
    {
        $gridMock = $this->createMock(Grid::class);
        $gridMock->method('addColumn')->willReturn($this->createStub(Column::class));
        $gridMock->method('createView')->willReturn($this->createStub(GridView::class));

        return $gridMock;
    }

    /**
     * The grid asks for no order while the records cannot be dragged, the records come by their position anyway.
     */
    private function createDataSourceExpectingOrderByPosition(): DataSourceInterface&MockObject
    {
        $dataSourceMock = $this->createMock(DataSourceInterface::class);
        $dataSourceMock
            ->expects($this->once())
            ->method('getPaginatedRows')
            ->with(null, 1, 'position', DataSourceInterface::ORDER_ASC)
            ->willReturn(new PaginationResult(1, null, 0, []));

        return $dataSourceMock;
    }

    /**
     * @param array<string, mixed>|null $quickSearchQuery
     * @param array<string, mixed>|null $filterQuery
     */
    private function createState(
        Datagrid $datagrid,
        ?array $quickSearchQuery = null,
        ?array $filterQuery = null,
    ): DatagridState {
        return DatagridStateFactoryFixture::createState($datagrid, DatagridStateFactoryFixture::createRequest($quickSearchQuery, $filterQuery));
    }

    private function createDatagrid(Grid $grid, DataSourceInterface $dataSource): Datagrid
    {
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturnCallback(function (string $name, DataSourceInterface $gridDataSource) use ($grid): Grid {
            $this->gridDataSource = $gridDataSource;

            return $grid;
        });

        $adapter = new class($dataSource) implements EntityClassAwareAdapterInterface {
            public function __construct(
                private readonly DataSourceInterface $dataSource,
            ) {
            }

            #[Override]
            public function getEntityClass(): string
            {
                return OrderableDummy::class;
            }

            #[Override]
            public function getDatasource(DatasourceRequest $request): DataSourceInterface
            {
                return $this->dataSource;
            }
        };

        $datagrid = new Datagrid($adapter, $gridFactoryStub, $this->createStub(AccessCheckerInterface::class), $this->createStub(AdministratorGridFacade::class), $this->createStub(Security::class), [
            'name' => DatagridStateFactoryFixture::GRID_ID,
            'roleConstant' => 'ROLE_CRUD_TEST',
        ]);
        $datagrid
            ->add('name', ['searchable' => true])
            ->enableDragAndDrop('position');

        return $datagrid;
    }
}
