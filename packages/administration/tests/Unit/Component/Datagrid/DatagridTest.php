<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use LogicException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\AdapterInterface;
use Shopsys\AdministrationBundle\Component\Datagrid\Adapter\DatasourceRequest;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\Column;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

class DatagridTest extends TestCase
{
    private const string DATAGRID_ROLE = 'ROLE_CRUD_TEST';

    private AdapterInterface&Stub $adapter;

    private GridFactory&Stub $gridFactory;

    /**
     * @var string[]
     */
    private array $fetchedFieldNames = [];

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Grid\Column>
     */
    private array $columnsById = [];

    #[Override]
    protected function setUp(): void
    {
        $this->adapter = $this->createStub(AdapterInterface::class);
        $this->adapter->method('getDatasource')->willReturnCallback(function (DatasourceRequest $request): DataSourceInterface {
            $this->fetchedFieldNames = array_map(static fn (FieldDescriptor $field) => $field->getName(), $request->fields);

            return $this->createStub(DataSourceInterface::class);
        });

        $grid = $this->createStub(Grid::class);
        $grid->method('addColumn')->willReturnCallback(function (string $id, string $sourceColumnName, string $title, bool $sortable, array $options): Column {
            $this->columnsById[$id] = new Column($id, $sourceColumnName, $title, $sortable, $options);

            return $this->columnsById[$id];
        });
        $grid->method('createView')->willReturn($this->createStub(GridView::class));

        $this->gridFactory = $this->createStub(GridFactory::class);
        $this->gridFactory->method('create')->willReturn($grid);
    }

    /**
     * @param array<array{string, \Shopsys\FrameworkBundle\Component\Security\Role\Permission, bool}> $permissionMap
     */
    private function createDatagrid(array $permissionMap = []): Datagrid
    {
        $accessChecker = $this->createStub(AccessCheckerInterface::class);
        $accessChecker->method('hasPermission')->willReturnMap($permissionMap);

        return new Datagrid($this->adapter, $this->gridFactory, $accessChecker, DatagridNarrowingResolverFactory::create(), DatagridRequestState::outsideRequest('datagrid'), null, ['roleConstant' => self::DATAGRID_ROLE]);
    }

    public function testRestrictedFieldIsNeitherFetchedNorDisplayedWithoutPermission(): void
    {
        $datagrid = $this->createDatagrid([
            ['ROLE_PRODUCT', Permission::VIEW, false],
            [self::DATAGRID_ROLE, Permission::EDIT, false],
        ]);

        $datagrid
            ->add('name')
            ->add('purchasePrice', ['role' => 'ROLE_PRODUCT'])
            ->add('internalNote', ['permission' => Permission::EDIT]);

        $datagrid->createView();

        $this->assertSame(['name'], $this->fetchedFieldNames);
        $this->assertSame(['name'], array_keys($this->columnsById));
    }

    public function testRestrictedFieldIsDisplayedWithPermission(): void
    {
        $datagrid = $this->createDatagrid([
            ['ROLE_PRODUCT', Permission::VIEW, true],
            [self::DATAGRID_ROLE, Permission::EDIT, true],
        ]);

        $datagrid
            ->add('name')
            ->add('purchasePrice', ['role' => 'ROLE_PRODUCT'])
            ->add('internalNote', ['permission' => Permission::EDIT]);

        $datagrid->createView();

        $this->assertSame(['name', 'purchasePrice', 'internalNote'], $this->fetchedFieldNames);
        $this->assertSame(['name', 'purchasePrice', 'internalNote'], array_keys($this->columnsById));
    }

    public function testUnrestrictedFieldsDoNotCheckPermissions(): void
    {
        $accessChecker = $this->createMock(AccessCheckerInterface::class);
        $accessChecker->expects($this->never())->method('hasPermission');
        $datagrid = new Datagrid($this->adapter, $this->gridFactory, $accessChecker, DatagridNarrowingResolverFactory::create(), DatagridRequestState::outsideRequest('datagrid'), null, ['roleConstant' => self::DATAGRID_ROLE]);

        $datagrid->add('name');

        $datagrid->createView();

        $this->assertSame(['name'], $this->fetchedFieldNames);
    }

    public function testClassIsSetToColumn(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid
            ->add('name')
            ->add('price', ['class' => 'text-end']);

        $datagrid->createView();

        $this->assertSame('', $this->columnsById['name']->getClassAttribute());
        $this->assertSame('text-end', $this->columnsById['price']->getClassAttribute());
    }

    public function testCombinedFieldIsOrderedByAllProperties(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('customer', ['property' => ['lastName', 'firstName']]);

        $datagrid->createView();

        $this->assertSame('customer', $this->columnsById['customer']->getSourceColumnName());
        $this->assertSame('lastName,firstName', $this->columnsById['customer']->getOrderSourceColumnName());
    }

    /**
     * The narrowing is resolved from the fields once, so a field changed afterwards — restricted by a role, for one —
     * would keep narrowing the records as it was; the change is refused instead.
     */
    public function testFieldsCannotBeChangedOnceTheNarrowingIsResolved(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name');
        $datagrid->getNarrowing();

        $changes = [
            static fn (Datagrid $datagrid) => $datagrid->add('email'),
            static fn (Datagrid $datagrid) => $datagrid->update('name', ['role' => 'ROLE_SECRET']),
            static fn (Datagrid $datagrid) => $datagrid->remove('name'),
        ];

        foreach ($changes as $change) {
            try {
                $change($datagrid);
                $this->fail('A change of the fields has to be refused once the narrowing is resolved.');
            } catch (LogicException $exception) {
                $this->assertStringContainsString('already resolved', $exception->getMessage());
            }
        }
    }
}
