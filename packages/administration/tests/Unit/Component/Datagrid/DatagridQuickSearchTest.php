<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Composite;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\LogicalOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlScope;
use Shopsys\AdministrationBundle\Component\Datagrid\DomainControl\DomainControlType;
use Shopsys\AdministrationBundle\Component\Datagrid\Exception\FieldNotSearchableException;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathCardinalityEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathDescription;
use Shopsys\AdministrationBundle\Component\Datagrid\Path\PathValueTypeEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

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

        $this->assertNull($datagrid->getQuickSearch());
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
        $this->assertSame([
            'name' => 'Name',
            'brand.name' => 'Brand',
        ], $datagrid->getQuickSearch()?->labelsByPath);
    }

    /**
     * The form is rendered even before anything is searched, so the quick search exists without a term.
     */
    public function testQuickSearchWithoutTermNarrowsNothing(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, null);
        $datagrid->add('name', ['searchable' => true]);

        $datagrid->createView();

        $this->assertNotNull($datagrid->getQuickSearch());
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testSearchIsCombinedWithTheDomainControlByConjunction(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $scope = new DomainControlScope(DomainControlType::FILTER, [1, 2], 1, 'crud_test', 'domainId');
        $datagrid = $this->createDatagrid($adapter, 'hrnek', $scope);
        $datagrid->add('name', ['searchable' => true]);

        $datagrid->createView();

        $condition = $adapter->getLastRequest()->condition;
        $this->assertInstanceOf(Composite::class, $condition);
        $this->assertSame(LogicalOperatorEnum::AND, $condition->operator);
        $this->assertEquals([$scope->createCondition(), Condition::orX(Condition::contains('name', 'hrnek'))], $condition->conditions);
    }

    public function testSearchableFieldIsRebuiltWhenTheFieldsChange(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
        $datagrid->add('name', ['searchable' => true]);
        $this->assertNotNull($datagrid->getQuickSearch());

        $datagrid->update('name', ['searchable' => false]);

        $this->assertNull($datagrid->getQuickSearch());
    }

    public function testDatagridOutsideRequestHasNoQuickSearch(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, null, null, false);
        $datagrid->add('name', ['searchable' => true]);

        $this->assertNull($datagrid->getQuickSearch());
    }

    public function testVirtualFieldWithoutPathIsRefused(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
        $datagrid->add('computed', ['searchable' => true, 'virtual' => true]);

        $this->expectException(FieldNotSearchableException::class);

        $datagrid->getQuickSearch();
    }

    public function testFieldLeadingToNonTextValueIsRefused(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class), [
            'createdAt' => new PathDescription('createdAt', PathCardinalityEnum::TO_ONE, PathValueTypeEnum::DATETIME),
        ]);
        $datagrid = $this->createDatagrid($adapter, 'hrnek');
        $datagrid->add('createdAt', ['searchable' => true]);

        $this->expectException(FieldNotSearchableException::class);
        $this->expectExceptionMessage('createdAt');

        $datagrid->getQuickSearch();
    }

    private function createDatagrid(
        CapturingAdapter $adapter,
        ?string $term,
        ?DomainControlScope $scope = null,
        bool $withinRequest = true,
    ): Datagrid {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        return new Datagrid($adapter, $gridFactoryStub, new ExpressionOperatorApplicability(), [
            'name' => 'ProductReview',
            'roleConstant' => 'ROLE_CRUD_TEST',
            'domainControlScope' => $scope,
            'requestState' => new DatagridRequestState($withinRequest ? $this->createQuickSearchForm($term) : null),
        ]);
    }

    private function createQuickSearchForm(?string $term): FormInterface
    {
        $data = new QuickSearchFormData();
        $data->text = $term;
        $formStub = $this->createStub(FormInterface::class);
        $formStub->method('getData')->willReturn($data);

        return $formStub;
    }
}
