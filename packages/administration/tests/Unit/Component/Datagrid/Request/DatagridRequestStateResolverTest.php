<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Request;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorApplicability;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\DatagridFilterFormType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterRuleType;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestStateResolver;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;

final class DatagridRequestStateResolverTest extends TestCase
{
    public function testSearchedTermIsReadFromTheFormNamedAfterTheDatagrid(): void
    {
        $state = $this->resolve('ProductReview', [
            'ProductReview_search' => [
                'text' => '  hrnek ',
            ],
        ]);

        $this->assertSame('hrnek', $state->getSearchTerm());
        $this->assertSame('ProductReview_search', $state->quickSearchForm?->getName());
    }

    public function testNothingIsSearchedWithoutTheParameter(): void
    {
        $state = $this->resolve('ProductReview', [
            'g' => [
                'ProductReview' => [
                    'page' => '2',
                ],
            ],
        ]);

        $this->assertNull($state->getSearchTerm());
        $this->assertNotNull($state->quickSearchForm, 'The form is built anyway, so that the component can render it.');
    }

    public function testBlankTermCountsAsNoSearch(): void
    {
        $state = $this->resolve('ProductReview', [
            'ProductReview_search' => [
                'text' => '   ',
            ],
        ]);

        $this->assertNull($state->getSearchTerm());
    }

    public function testTheFormOfAnotherDatagridIsNotRead(): void
    {
        $state = $this->resolve('Order', [
            'ProductReview_search' => [
                'text' => 'hrnek',
            ],
        ]);

        $this->assertNull($state->getSearchTerm());
    }

    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    public function testFormNameIsSafeForAnyDatagridId(): void
    {
        $this->assertSame('Product_review_search', DatagridRequestState::createQuickSearchFormName('Product review'));
    }

    public function testFilterFormStartsWithOneGroupAndOneRuleUntilSomeRuleIsComposed(): void
    {
        $filters = $this->createFilters();

        $emptyForm = $this->resolve('ProductReview', [])->createFilterForm($filters);
        $submittedEmptyForm = $this->resolve('ProductReview', ['ProductReview_filter' => ['groups' => []]])->createFilterForm($filters);
        $composedForm = $this->resolve('ProductReview', ['ProductReview_filter' => ['groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]]]])->createFilterForm($filters);

        foreach ([$emptyForm, $submittedEmptyForm] as $form) {
            $this->assertNotNull($form);
            $this->assertFalse($form->isSubmitted());
            $this->assertCount(1, $form->getData()->groups);
            $this->assertCount(1, $form->getData()->groups[0]->rules);
        }
        $this->assertNotNull($composedForm);
        $this->assertTrue($composedForm->isSubmitted());
        $this->assertSame('x', $composedForm->getData()->groups[0]->rules[0]->value);
    }

    public function testDatagridOutsideRequestHasNoForm(): void
    {
        $resolver = new DatagridRequestStateResolver(Forms::createFormFactory(), new RequestStack());

        $state = $resolver->resolve('ProductReview');

        $this->assertNull($state->quickSearchForm);
        $this->assertNull($state->getSearchTerm());
    }

    /**
     * @param array<string, mixed> $query
     */
    private function resolve(string $gridId, array $query): DatagridRequestState
    {
        $requestStack = new RequestStack();
        $requestStack->push(new Request($query));
        $formFactory = Forms::createFormFactoryBuilder()
            ->addExtension(new ValidatorExtension(Validation::createValidator()))
            ->addType(new FilterRuleType())
            ->addType(new DatagridFilterFormType())
            ->getFormFactory();

        return (new DatagridRequestStateResolver($formFactory, $requestStack))->resolve($gridId);
    }

    private function createFilters(): FilterCollection
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);

        $filters = new FilterCollection();
        $filters->add(TextFilter::new('name'));
        $filters->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new ExpressionOperatorApplicability(),
            new DateTimeZone('Europe/Prague'),
        ));

        return $filters;
    }
}
