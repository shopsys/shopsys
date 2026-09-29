<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid;

use DateTimeZone;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Datagrid;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Grid\Grid;
use Shopsys\FrameworkBundle\Component\Grid\GridFactory;
use Shopsys\FrameworkBundle\Component\Grid\GridView;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Shopsys\FrameworkBundle\Form\Admin\QuickSearch\QuickSearchFormData;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridFilterTest extends TestCase
{
    private FormFactoryInterface $formFactory;

    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);

        // the prototypes of the form opt out of validation, which is an option of the validator extension
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType(new FilterRuleType())
            ->addType(new DatagridFilterFormType())
            ->getFormFactory();
    }

    public function testDatagridWithoutFiltersHasNoFilterForm(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]);

        $datagrid->createView();

        $this->assertNull($datagrid->getFilterForm());
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testComposedRulesNarrowTheDatagrid(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, ['operator' => 'and', 'groups' => [
            ['operator' => 'or', 'rules' => [
                ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                ['filter' => 'price', 'operator' => 'greaterThan', 'value' => '100'],
            ]],
        ]]);
        $this->declareFilters($datagrid);

        $datagrid->createView();

        $this->assertTrue($datagrid->hasFilterRules());
        $this->assertEquals(
            Condition::orX(Condition::contains('name', 'hrnek'), Condition::greaterThan('price', 100.0)),
            $adapter->getLastRequest()->condition,
        );
    }

    /**
     * The filter and the quick search are two ways of asking the same question, so a composed filter wins
     * and the quick search is neither applied nor shown.
     */
    public function testComposedFilterOutranksTheQuickSearch(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid(
            $adapter,
            ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'equals', 'value' => 'Hrnek']]]]],
            'talíř',
        );
        $this->declareFilters($datagrid);
        $datagrid->add('name', ['searchable' => true]);

        $datagrid->createView();

        $this->assertEquals(Condition::equals('name', 'Hrnek'), $adapter->getLastRequest()->condition);
        $this->assertNull($datagrid->getQuickSearch()?->term);
    }

    public function testQuickSearchAppliesWhenNoRuleIsComposed(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, ['groups' => []], 'talíř');
        $this->declareFilters($datagrid);
        $datagrid->add('name', ['searchable' => true]);

        $datagrid->createView();

        $this->assertFalse($datagrid->hasFilterRules());
        $this->assertEquals(Condition::orX(Condition::contains('name', 'talíř')), $adapter->getLastRequest()->condition);
    }

    /**
     * An invalid filter narrows nothing and shows its errors, instead of quietly listing more than the
     * administrator asked for; the quick search stays outranked.
     */
    public function testInvalidFilterNarrowsNothing(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid(
            $adapter,
            ['groups' => [['rules' => [['filter' => 'price', 'operator' => 'contains', 'value' => 'x']]]]],
            'talíř',
        );
        $this->declareFilters($datagrid);
        $datagrid->add('name', ['searchable' => true]);

        $datagrid->createView();

        $this->assertTrue($datagrid->hasFilterRules());
        $this->assertFalse($datagrid->getFilterForm()?->isValid());
        $this->assertNull($adapter->getLastRequest()->condition);
    }

    public function testDatagridOutsideRequestHasNoFilterForm(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, null);
        $this->declareFilters($datagrid);

        $this->assertNull($datagrid->getFilterForm());
        $this->assertFalse($datagrid->hasFilterRules());
    }

    public function testDeclaredFiltersNeedTheEnvironmentOfTheDatagrid(): void
    {
        $adapter = new CapturingAdapter($this->createStub(DataSourceInterface::class));
        $datagrid = $this->createDatagrid($adapter, ['groups' => []], null, false);
        $this->declareFilters($datagrid);

        $this->expectException(InvalidArgumentException::class);

        $datagrid->getFilterForm();
    }

    private function declareFilters(Datagrid $datagrid): void
    {
        $datagrid->filters()
            ->add(TextFilter::new('name'))
            ->add(NumericFilter::new('price'));
    }

    /**
     * @param array<string, mixed>|null $submittedFilter Null when the datagrid is built outside a request
     */
    private function createDatagrid(
        CapturingAdapter $adapter,
        ?array $submittedFilter,
        ?string $searchTerm = null,
        bool $withEnvironment = true,
    ): Datagrid {
        $gridStub = $this->createStub(Grid::class);
        $gridStub->method('createView')->willReturn($this->createStub(GridView::class));
        $gridFactoryStub = $this->createStub(GridFactory::class);
        $gridFactoryStub->method('create')->willReturn($gridStub);

        $requestState = $submittedFilter === null
            ? new DatagridRequestState()
            : new DatagridRequestState(
                $this->createQuickSearchForm($searchTerm),
                fn (FilterCollection $filters): FormInterface => $this->createSubmittedFilterForm($filters, $submittedFilter),
            );

        return new Datagrid($adapter, $gridFactoryStub, new ExpressionOperatorApplicability(), [
            'name' => 'Product',
            'roleConstant' => 'ROLE_CRUD_TEST',
            'requestState' => $requestState,
            'filterEnvironment' => $withEnvironment
                ? new FilterEnvironment($adapter, new ExpressionOperatorEnum(), new ExpressionOperatorApplicability(), new DateTimeZone('Europe/Prague'))
                : null,
        ]);
    }

    /**
     * @param array<string, mixed> $submittedFilter
     */
    private function createSubmittedFilterForm(FilterCollection $filters, array $submittedFilter): FormInterface
    {
        $form = $this->formFactory->createNamed('Product_filter', DatagridFilterFormType::class, new FilterFormData(), [
            'filters' => $filters,
        ]);
        $form->submit($submittedFilter);

        return $form;
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
