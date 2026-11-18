<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\Form;

use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\Data\FilterFormData;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterGroupType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRangeType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\NumericFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class DatagridFilterFormTypeTest extends TestCase
{
    use SetTranslatorTrait;

    private const array RULE = [
        'filter' => 'name',
        'operator' => 'contains',
        'value' => 'hrnek',
    ];

    private FormFactoryInterface $formFactory;

    private FilterCollection $filters;

    #[Override]
    protected function setUp(): void
    {
        $this->setTranslator();

        $this->formFactory = DatagridStateFactoryFixture::createFormFactory();

        $environment = new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class), CapturingAdapter::getFilterPathDescriptions()),
            new ExpressionOperatorEnum(),
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
        $this->assertSame(['The selected choice is invalid.'], $this->getMessages($this->getRule($form)->get('operator')));
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
        $this->assertSame(['The selected choice is invalid.'], $this->getMessages($this->getRule($form)->get('filter')));
        $this->assertSame(['The selected choice is invalid.'], $this->getMessages($this->getRule($form)->get('operator')));
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
        foreach (['yes', 'no'] as $operator) {
            $form = $this->createForm();

            $form->submit([
                'groups' => [
                    ['rules' => [['filter' => 'inStock', 'operator' => $operator, 'value' => 'stale']]],
                ],
            ]);

            $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
            $this->assertNull($form->getData()->groups[0]->rules[0]->value);
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

    public function testBoundsOfRangeKeepTheOptionsOfTheValue(): void
    {
        $range = $this->formFactory->create(FilterRangeType::class, null, [
            'entry_type' => TextType::class,
            'entry_options' => ['required' => true],
        ]);

        $this->assertTrue($range->get('from')->getConfig()->getRequired());
        $this->assertTrue($range->get('to')->getConfig()->getRequired());
    }

    public function testFormWithoutRulesIsValidAndEmpty(): void
    {
        $form = $this->createForm();

        $form->submit(['groups' => []]);

        $this->assertTrue($form->isValid());
        $this->assertFalse($form->getData()->hasRules());
    }

    /**
     * A collection builds a form for every submitted entry, so a query edited by hand to a hundred groups builds
     * only one group over the limit. The error is on the form itself, which the page shows above the groups.
     */
    public function testGroupsOverTheLimitAreAnError(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => array_fill(0, 100, ['rules' => [self::RULE]]),
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(DatagridFilterFormType::MAX_GROUPS + 1, $form->get('groups'));
        $this->assertSame(['The filter can have at most 10 groups of rules.'], $this->getMessages($form));
    }

    public function testRulesOverTheLimitAreAnError(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => [
                ['rules' => array_fill(0, 100, self::RULE)],
            ],
        ]);

        $this->assertFalse($form->isValid());
        $this->assertCount(FilterGroupType::MAX_RULES + 1, $form->get('groups')->get('0')->get('rules'));
        $this->assertSame(['A group of rules can have at most 10 rules.'], $this->getMessages($form));
    }

    public function testGroupsAndRulesUpToTheLimitAreValid(): void
    {
        $form = $this->createForm();

        $form->submit([
            'groups' => array_fill(0, DatagridFilterFormType::MAX_GROUPS, [
                'rules' => array_fill(0, FilterGroupType::MAX_RULES, self::RULE),
            ]),
        ]);

        $this->assertTrue($form->isValid(), (string)$form->getErrors(true));
        $this->assertCount(DatagridFilterFormType::MAX_GROUPS, $form->getData()->groups);
        $this->assertCount(FilterGroupType::MAX_RULES, $form->getData()->groups[0]->rules);
    }

    /**
     * @return string[]
     */
    private function getMessages(FormInterface $form): array
    {
        return array_map(static fn (FormError $error): string => $error->getMessage(), iterator_to_array($form->getErrors()));
    }

    private function getRule(FormInterface $form): FormInterface
    {
        return $form->get('groups')->get('0')->get('rules')->get('0');
    }

    private function createForm(): FormInterface
    {
        return $this->formFactory->createNamed('Product_filter', DatagridFilterFormType::class, new FilterFormData(), [
            'filters' => $this->filters,
        ]);
    }
}
