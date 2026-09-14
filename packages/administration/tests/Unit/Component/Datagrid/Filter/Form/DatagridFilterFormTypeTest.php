<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\Form;

use DateTimeZone;
use Override;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Condition\Condition;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\BooleanFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
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

        $this->filters = new FilterCollection();
        $this->filters
            ->add(TextFilter::new('name', 'Name'))
            ->add(NumericFilter::new('price', 'Price'))
            ->add(BooleanFilter::new('inStock', 'In stock'));
        $this->filters->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            $expressionOperatorEnum,
            new ExpressionOperatorApplicability(),
            new DateTimeZone('Europe/Prague'),
        ));
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
     * The prototypes of every filter are part of the form but never of the submission, so a filter with
     * a demanding value type (a yes/no choice) must not fail the form it is not used in.
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
            $this->assertEquals(Condition::equals('inStock', $expected), $this->filters->createCondition($form->getData()));
        }

        $this->assertSame(['none'], array_keys(iterator_to_array($this->arityPrototypes($this->createForm(), 'inStock'))));
    }

    public function testFormWithoutRulesIsValidAndEmpty(): void
    {
        $form = $this->createForm();

        $form->submit(['groups' => []]);

        $this->assertTrue($form->isValid());
        $this->assertFalse($form->getData()->hasRules());
    }

    /**
     * The page swaps the operation and the value of a rule from these, so every filter has one per arity of its operations.
     */
    public function testPrototypesAreRenderedForEveryFilterAndArityWithoutTouchingTheData(): void
    {
        $form = $this->createForm();
        $view = $form->createView();

        // the arities follow the order of the operations of the filter: equals ... (single), between (range), isNull (none)
        $this->assertSame(['single', 'range', 'none'], array_keys(iterator_to_array($this->arityPrototypes($form, 'price'))));
        $this->assertArrayHasKey('value', $view['prototypes']['price']['range']->children);
        $this->assertArrayNotHasKey('value', $view['prototypes']['price']['none']->children);
        $this->assertArrayHasKey('value', $view['prototypes']['name']['single']->children);
        $this->assertSame('range', $view['prototypes']['price']['range']['operator']->vars['choices'][6]->attr['data-arity']);
    }

    private function createForm(): FormInterface
    {
        return $this->formFactory->createNamed('Product_filter', DatagridFilterFormType::class, new FilterFormData(), [
            'filters' => $this->filters,
        ]);
    }

    /**
     * @return iterable<string, \Symfony\Component\Form\FormInterface>
     */
    private function arityPrototypes(FormInterface $form, string $filterName): iterable
    {
        foreach ($form->get(DatagridFilterFormType::PROTOTYPES_NAME)->get($filterName) as $arity => $prototype) {
            yield $arity => $prototype;
        }
    }
}
