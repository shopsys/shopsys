<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use LogicException;
use Override;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlConfig;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScopeFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlStateSubscriber;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Field\FieldDescriptor;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\GuessedFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridStateFactory;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainFilterTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\AdminDomainTabsFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Grid\Column;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Component\Security\Role\Permission;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;
use Webmozart\Assert\InvalidArgumentException;

class DatagridTest extends TestCase
{
    use SetTranslatorTrait;

    private const string DATAGRID_ROLE = 'ROLE_CRUD_TEST';

    private CapturingAdapter $adapter;

    private GridFactory&Stub $gridFactory;

    /**
     * @var array<string, \Shopsys\FrameworkBundle\Component\Grid\Column>
     */
    private array $columnsById = [];

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
        $this->adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));

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
    private function createDatagrid(
        array $permissionMap = [],
        ?DomainControlConfig $domainControlConfig = null,
    ): Datagrid {
        $accessChecker = $this->createStub(AccessCheckerInterface::class);
        $accessChecker->method('hasPermission')->willReturnMap($permissionMap);

        return new Datagrid($this->adapter, $this->gridFactory, $accessChecker, $this->createStub(AdministratorGridFacade::class), $this->createStub(Security::class), [
            'name' => DatagridStateFactoryFixture::GRID_ID,
            'roleConstant' => self::DATAGRID_ROLE,
            'domainControl' => $domainControlConfig,
        ]);
    }

    /**
     * @return string[]
     */
    private function getFetchedFieldNames(): array
    {
        return array_keys($this->adapter->getLastRequestFields());
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

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid));

        $this->assertSame(['name'], $this->getFetchedFieldNames());
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

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid));

        $this->assertSame(['name', 'purchasePrice', 'internalNote'], $this->getFetchedFieldNames());
        $this->assertSame(['name', 'purchasePrice', 'internalNote'], array_keys($this->columnsById));
    }

    public function testUnrestrictedFieldsDoNotCheckPermissions(): void
    {
        $accessChecker = $this->createMock(AccessCheckerInterface::class);
        $accessChecker->expects($this->never())->method('hasPermission');
        $datagrid = new Datagrid($this->adapter, $this->gridFactory, $accessChecker, $this->createStub(AdministratorGridFacade::class), $this->createStub(Security::class), ['roleConstant' => self::DATAGRID_ROLE]);

        $datagrid->add('name');

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid));

        $this->assertSame(['name'], $this->getFetchedFieldNames());
    }

    public function testClassIsSetToColumn(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid
            ->add('name')
            ->add('price', ['class' => 'text-end']);

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid));

        $this->assertSame('', $this->columnsById['name']->getClassAttribute());
        $this->assertSame('text-end', $this->columnsById['price']->getClassAttribute());
    }

    public function testCombinedFieldIsOrderedByAllProperties(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('customer', ['property' => ['lastName', 'firstName']]);

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid));

        $this->assertSame('customer', $this->columnsById['customer']->getSourceColumnName());
        $this->assertSame('lastName,firstName', $this->columnsById['customer']->getOrderSourceColumnName());
    }

    /**
     * The state is created from the configuration frozen once, so a field changed afterwards — restricted by a role,
     * for one — would keep narrowing the records as it was; the change is refused instead.
     */
    public function testFieldsCannotBeChangedOnceTheConfigurationIsFrozen(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name');
        $datagrid->getConfig();

        $changes = [
            static fn (Datagrid $datagrid) => $datagrid->add('email'),
            static fn (Datagrid $datagrid) => $datagrid->update('name', ['role' => 'ROLE_SECRET']),
            static fn (Datagrid $datagrid) => $datagrid->remove('name'),
            static fn (Datagrid $datagrid) => $datagrid->filters(),
        ];

        foreach ($changes as $change) {
            try {
                $change($datagrid);
                $this->fail('A change of the fields has to be refused once the configuration is frozen.');
            } catch (LogicException $exception) {
                $this->assertStringContainsString('already frozen', $exception->getMessage());
            }
        }
    }

    public function testConfigurationIsFrozenOnce(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name');

        $this->assertSame($datagrid->getConfig(), $datagrid->getConfig());
    }

    /**
     * A field displayed on behalf of a feature narrowing the datagrid obeys the permissions as any other field.
     */
    public function testRestrictedAdditionalFieldIsNotDisplayedWithoutPermission(): void
    {
        $datagrid = $this->createDatagrid([['ROLE_SECRET', Permission::VIEW, false]]);
        $datagrid->add('name');

        $datagrid->createView(new DatagridState($datagrid->getConfig(), additionalFields: [
            new FieldDescriptor('secret', ['role' => 'ROLE_SECRET']),
            new FieldDescriptor('domainId'),
        ]));

        $this->assertSame(['name', 'domainId'], $this->getFetchedFieldNames());
    }

    public function testFilterableFieldAddsItsFilterToTheFilters(): void
    {
        $datagrid = $this->createDatagrid();

        $datagrid
            ->add('customerUser.email', ['filterable' => true])
            ->add('name');

        $this->assertInstanceOf(GuessedFilter::class, $datagrid->filters()->get('customerUser_email'));
        $this->assertFalse($datagrid->filters()->has('name'));
    }

    public function testUpdateSwitchesTheFilterOfTheField(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name');

        $datagrid->update('name', ['filterable' => true]);
        $this->assertInstanceOf(GuessedFilter::class, $datagrid->filters()->get('name'));

        $datagrid->update('name', ['filterable' => false]);
        $this->assertFalse($datagrid->filters()->has('name'));
    }

    public function testRemovedFieldTakesItsFilterAlong(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name', ['filterable' => true]);

        $datagrid->remove('name');

        $this->assertFalse($datagrid->filters()->has('name'));
    }

    public function testFilterOfFieldRemovedFromTheFiltersStaysRemoved(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name', ['filterable' => true]);

        $datagrid->filters()->remove('name');
        $datagrid->update('name', ['label' => 'Title']);

        $this->assertFalse($datagrid->filters()->has('name'));
    }

    /**
     * The filter name replaces the dots of the field name, so `customerUser.email` and `customerUser_email`
     * would share one filter.
     */
    public function testTwoFilterableFieldsCannotShareTheNameOfTheirFilter(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('customerUser.email', ['filterable' => true]);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('cannot both be filterable');

        $datagrid->add('customerUser_email', ['filterable' => true]);
    }

    public function testFilterDeclaredAfterTheFieldReplacesTheFilterOfTheField(): void
    {
        $datagrid = $this->createDatagrid();
        $filter = TextFilter::new('status');

        $datagrid->add('status', ['filterable' => true]);
        $datagrid->filters()->add($filter);

        $this->assertSame($filter, $datagrid->filters()->get('status'));
    }

    public function testFilterDeclaredBeforeTheFieldTakesPrecedenceOverTheFilterOfTheField(): void
    {
        $datagrid = $this->createDatagrid();
        $filter = TextFilter::new('status');

        $datagrid->filters()->add($filter);
        $datagrid->add('status', ['filterable' => true]);

        $this->assertSame($filter, $datagrid->filters()->get('status'));
    }

    public function testDeclaredFilterOfTheFieldStaysWhenTheFieldStopsBeingFilterableOrIsRemoved(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('status', ['filterable' => true]);
        $filter = TextFilter::new('status');
        $datagrid->filters()->add($filter);

        $datagrid->update('status', ['filterable' => false]);
        $datagrid->update('status', ['filterable' => true]);
        $datagrid->remove('status');

        $this->assertSame($filter, $datagrid->filters()->get('status'));
    }

    /**
     * The search could otherwise tell what a field the administrator is not allowed to see holds.
     */
    public function testRestrictedFieldIsNotSearchedInWithoutPermission(): void
    {
        $datagrid = $this->createDatagrid([['ROLE_PURCHASE_PRICES', Permission::VIEW, false]]);
        $datagrid
            ->add('name', ['searchable' => true])
            ->add('purchasePrice', ['searchable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $datagrid->createView(DatagridStateFactoryFixture::createState($datagrid, DatagridStateFactoryFixture::createRequest(['text' => 'hrnek'])));

        $this->assertEquals(Condition::orX(Condition::contains('name', 'hrnek')), $this->adapter->getLastRequest()->condition);
    }

    /**
     * Hiding the field of the domain never widens the listed records.
     */
    public function testDomainControlLimitsTheRecordsToTheSelectedDomain(): void
    {
        $adminDomainFilterTabsFacadeStub = $this->createStub(AdminDomainFilterTabsFacade::class);
        $adminDomainFilterTabsFacadeStub->method('getSelectedDomainId')->willReturn(2);
        $domainStub = $this->createStub(Domain::class);
        $domainStub->method('getAdminEnabledDomainIds')->willReturn([1, 2, 3]);
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new DomainControlStateSubscriber(
            new DomainControlScopeFactory($adminDomainFilterTabsFacadeStub, $this->createStub(AdminDomainTabsFacade::class), $domainStub),
        ));

        $datagrid = $this->createDatagrid(domainControlConfig: new DomainControlConfig(DomainControlType::FILTER, null, 'crud_test', 'domainId'));
        $datagrid
            ->add('name')
            ->add('domainId', ['visible' => false]);

        $datagrid->createView(new DatagridStateFactory($eventDispatcher, new RequestStack())->create($datagrid->getConfig()));

        $this->assertEquals(Condition::in('domainId', [2]), $this->adapter->getLastRequest()->condition);
    }

    public function testDomainFieldIsLastByDefault(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid->add('name');

        $datagrid->createView($this->createStateWithDomainField($datagrid));

        $this->assertSame(['name', 'domainId'], $this->getFetchedFieldNames());
    }

    public function testManuallyAddedDomainFieldTakesPrecedence(): void
    {
        $datagrid = $this->createDatagrid();
        $datagrid
            ->add('domainId', ['label' => 'Custom domain'])
            ->add('name');

        $datagrid->createView($this->createStateWithDomainField($datagrid));

        $fields = $this->adapter->getLastRequestFields();
        $this->assertSame(['domainId', 'name'], array_keys($fields));
        $this->assertSame('Custom domain', $fields['domainId']->getLabel());
        $this->assertNull($fields['domainId']->getTemplate());
    }

    public function testManuallyAddedDomainFieldHiddenFromAdministratorIsNotReplaced(): void
    {
        $datagrid = $this->createDatagrid([['ROLE_SECRET', Permission::VIEW, false]]);
        $datagrid
            ->add('domainId', ['role' => 'ROLE_SECRET'])
            ->add('name');

        $datagrid->createView($this->createStateWithDomainField($datagrid));

        $this->assertSame(['name'], $this->getFetchedFieldNames());
    }

    public function testStateOfAnotherDatagridIsRefused(): void
    {
        $datagrid = $this->createDatagrid();
        $anotherDatagrid = $this->createDatagrid();

        $this->expectException(InvalidArgumentException::class);

        $datagrid->createView(DatagridStateFactoryFixture::createState($anotherDatagrid));
    }

    private function createStateWithDomainField(Datagrid $datagrid): DatagridState
    {
        return new DatagridState($datagrid->getConfig(), additionalFields: [
            new FieldDescriptor('domainId', ['template' => '@ShopsysAdministration/datagrid/cell/domain_icon.html.twig']),
        ]);
    }
}
