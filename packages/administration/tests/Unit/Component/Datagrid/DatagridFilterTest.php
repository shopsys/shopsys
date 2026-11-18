<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\InvalidDatagridDeclarationException;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\State\DatagridState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Security\AccessControl\AccessCheckerInterface;
use Shopsys\FrameworkBundle\Model\Administrator\AdministratorGridFacade;
use Symfony\Bundle\SecurityBundle\Security;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class DatagridFilterTest extends TestCase
{
    use SetTranslatorTrait;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();
    }

    /**
     * No filter can be picked for the virtual `margin`, which must not break the datagrid of an administrator
     * who cannot see it.
     */
    public function testFilterIsNotPickedForRestrictedFieldWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('price', ['filterable' => true])
            ->add('margin', ['virtual' => true, 'filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);

        $this->assertSame(['price'], $this->getOfferedFilterNames($this->createState($datagrid, [])));
    }

    /**
     * The filter is picked when the state is created, so the field can still be changed or removed until then.
     */
    public function testFieldWhoseFilterCannotBePickedIsRefusedWhenTheStateIsCreated(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter);
        $datagrid->add('margin', ['virtual' => true, 'filterable' => true]);
        $datagrid->getConfig();

        $this->expectException(InvalidDatagridDeclarationException::class);
        $this->expectExceptionMessage('Field "margin" cannot be filterable: a virtual field has no path');

        $this->createState($datagrid, []);
    }

    public function testFilterOfFieldRemovedFromTheFiltersIsNotOffered(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter);
        $datagrid
            ->add('price', ['filterable' => true])
            ->add('inStock', ['filterable' => true]);

        $datagrid->filters()->remove('price');

        $this->assertSame(['inStock'], $this->getOfferedFilterNames($this->createState($datagrid, [])));
    }

    /**
     * A filter declared under the name of the field replaces the one picked for it, so it is left out with the field
     * even when it compares another column. So is a filter over a property of the field under another name (`cost`).
     */
    public function testFilterDeclaredForRestrictedFieldIsNotOfferedWithoutPermission(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions());
        $datagrid = $this->createDatagrid($adapter, false);
        $datagrid
            ->add('name', ['filterable' => true])
            ->add('purchasePrice', ['filterable' => true, 'role' => 'ROLE_PURCHASE_PRICES']);
        $datagrid->filters()
            ->add(NumericFilter::new('price')->setName('purchasePrice'))
            ->add(NumericFilter::new('purchasePrice')->setName('cost'));

        $state = $this->createState($datagrid, [
            'groups' => [['rules' => [['filter' => 'purchasePrice', 'operator' => 'greaterThan', 'value' => '100']]]],
        ]);
        $datagrid->createView($state);

        $this->assertSame(['name'], $this->getOfferedFilterNames($state));
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    /**
     * A filter under a hidden property (`customerUser.email`) or above it (`address`) reads what the field holds.
     * A filter over another branch of the association (`address.city`) does not.
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
