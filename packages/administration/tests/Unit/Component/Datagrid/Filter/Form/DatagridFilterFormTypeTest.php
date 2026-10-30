<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\Form;

use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterPanel;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Validator\Validation;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridFilterFormTypeTest extends TestCase
{
    private FormFactoryInterface $formFactory;

    private FilterCollection $filters;

    #[Override]
    protected function setUp(): void
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);

        $expressionOperatorEnum = new ExpressionOperatorEnum();
        $this->formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType(new FilterRuleType())
            ->addType(new DatagridFilterFormType())
            ->getFormFactory();

        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            $expressionOperatorEnum,
            new DateTimeZone('Europe/Prague'),
        );
        $this->filters = new FilterCollection();

        foreach ([TextFilter::new('name', 'Name'), NumericFilter::new('price', 'Price'), BooleanFilter::new('inStock', 'In stock')] as $filter) {
            $filter->resolveFor($environment);
            $this->filters->add($filter);
        }
    }

    public function testSubmittedRulesAreReadWithTheValueShapedByTheOperation(): void
    {
        $form = $this->createForm();

        $form->submit([
            'operator' => 'or',
            'groups' => [
                ['operator' => 'and', 'rules' => [
                    ['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek'],
                    ['filter' => 'price', 'operator' => 'between', 'value' => ['from' => '10', 'to' => '20']],
                ]],
                ['operator' => 'and', 'rules' => [
                    ['filter' => 'price', 'operator' => 'isNull'],
                ]],
            ],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
        /** @var \Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData $data */
        $data = $form->getData();
        $this->assertSame('or', $data->operator);
        $this->assertCount(2, $data->groups);
        $this->assertSame('hrnek', $data->groups[0]->rules[0]->value);
        $this->assertSame(['from' => 10.0, 'to' => 20.0], $data->groups[0]->rules[1]->value);
        $this->assertSame('isNull', $data->groups[1]->rules[0]->operator);
        $this->assertNull($data->groups[1]->rules[0]->value);
        $this->assertTrue($data->hasRules());
    }

    public function testOperationNotOfferedByTheFilterIsAnError(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => [
                ['rules' => [['filter' => 'price', 'operator' => 'contains', 'value' => 'x']]],
            ],
        ]);

        $this->assertFalse($form->isValid());
    }

    /**
     * A query edited by hand never breaks the page, the rule is reported as invalid instead.
     */
    public function testListInsteadOfNameIsAnError(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => [
                ['rules' => [['filter' => ['name'], 'operator' => ['contains'], 'value' => 'x']]],
            ],
        ]);

        $this->assertFalse($form->isValid());
    }

    /**
     * The choices are the names of the filters, so a label shared by two filters (the name of a customer and of
     * a product) never hides one of them.
     */
    public function testFiltersOfOneLabelAreBothOffered(): void
    {
        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        );
        $filters = new FilterCollection();

        foreach ([TextFilter::new('customer.name', 'Name'), TextFilter::new('product.name', 'Name')] as $filter) {
            $filter->resolveFor($environment);
            $filters->add($filter);
        }

        $form = $this->formFactory->createNamed('Product_filter', DatagridFilterFormType::class, FilterFormData::createStartingPoint(), [
            'filters' => $filters,
        ]);
        $filterChoices = $form->createView()['groups'][0]['rules'][0]['filter']->vars['choices'];

        $this->assertSame(['customer_name', 'product_name'], array_map(static fn ($choice) => $choice->value, $filterChoices));
        $this->assertSame(['Name', 'Name'], array_map(static fn ($choice) => $choice->label, $filterChoices));
    }

    /**
     * Only the composed rules are validated, so a filter with a demanding value type (a yes/no choice) must not
     * fail the form it is not used in.
     */
    public function testUnusedFilterDoesNotInvalidateTheForm(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => [
                ['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'hrnek']]],
            ],
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
    }

    /**
     * The yes/no choice is the operation itself, so the rule has no value field and a stale value is ignored.
     */
    public function testBooleanRuleIsDecidedByItsOperationAlone(): void
    {
        foreach (['yes' => true, 'no' => false] as $operator => $expected) {
            $form = $this->createForm();

            $form->submit([
                'groups' => [
                    ['rules' => [['filter' => 'inStock', 'operator' => $operator, 'value' => 'stale']]],
                ],
            ]);

            $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
            $this->assertNull($form->getData()->groups[0]->rules[0]->value);
            $this->assertEquals(Condition::equals('inStock', $expected), new FilterPanel($form, $this->filters)->createCondition());
        }
    }

    /**
     * A range missing one of its bounds would narrow nothing, so it is an error; a range missing both is a rule
     * without a value, which narrows nothing as any other.
     */
    public function testRangeMissingOneOfItsBoundsIsAnError(): void
    {
        $halfFilledForm = $this->createForm();
        $halfFilledForm->submit([
            'groups' => [
                ['rules' => [['filter' => 'price', 'operator' => 'between', 'value' => ['from' => '10', 'to' => '']]]],
            ],
        ]);

        $emptyForm = $this->createForm();
        $emptyForm->submit([
            'groups' => [
                ['rules' => [['filter' => 'price', 'operator' => 'between', 'value' => ['from' => '', 'to' => '']]]],
            ],
        ]);

        $this->assertFalse($halfFilledForm->isValid());
        $this->assertTrue($emptyForm->isValid(), (string)$emptyForm->getErrors(true));
    }

    public function testFormWithoutRulesIsValidAndEmpty(): void
    {
        $form = $this->createForm();

        $form->submit(['groups' => []]);

        $this->assertTrue($form->isValid());
        $this->assertFalse($form->getData()->hasRules());
    }

    private function createForm(): FormInterface
    {
        return $this->formFactory->createNamed('Product_filter', DatagridFilterFormType::class, new FilterFormData(), [
            'filters' => $this->filters,
        ]);
    }
}
