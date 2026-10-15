<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Request;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridNarrowingResolverFactory;

final class DatagridFormFactoryTest extends TestCase
{
    public function testQuickSearchFormIsSubmittedFromTheQuery(): void
    {
        $form = $this->createFormFactory()->createQuickSearchForm(new DatagridRequestState('ProductReview', ['text' => '  hrnek ']));

        $this->assertSame('ProductReview_search', $form->getName());
        $this->assertTrue($form->isSubmitted());
        $this->assertSame('hrnek', $this->createFormFactory()->getSearchTerm($form));
    }

    public function testQuickSearchFormWithoutQueryIsNotSubmitted(): void
    {
        $form = $this->createFormFactory()->createQuickSearchForm(new DatagridRequestState('ProductReview'));

        $this->assertFalse($form->isSubmitted());
        $this->assertNull($this->createFormFactory()->getSearchTerm($form));
    }

    public function testBlankTermCountsAsNoSearch(): void
    {
        $form = $this->createFormFactory()->createQuickSearchForm(new DatagridRequestState('ProductReview', ['text' => '   ']));

        $this->assertNull($this->createFormFactory()->getSearchTerm($form));
    }

    public function testComposedFilterIsSubmittedFromTheQuery(): void
    {
        $form = $this->createFormFactory()->createFilterForm($this->createFilters(), new DatagridRequestState('ProductReview', null, [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]));

        $this->assertSame('ProductReview_filter', $form->getName());
        $this->assertTrue($form->isSubmitted());
        $this->assertSame('x', $form->getData()->groups[0]->rules[0]->value);
    }

    /**
     * A filter with no rule composed — never submitted, or submitted empty — is shown as the starting point,
     * one group with one rule, and counts as not submitted.
     */
    public function testFilterFormStartsWithOneGroupAndOneRuleUntilSomeRuleIsComposed(): void
    {
        $filters = $this->createFilters();

        foreach ([new DatagridRequestState('ProductReview'), new DatagridRequestState('ProductReview', null, ['groups' => []])] as $state) {
            $form = $this->createFormFactory()->createFilterForm($filters, $state);

            $this->assertFalse($form->isSubmitted());
            $this->assertCount(1, $form->getData()->groups);
            $this->assertCount(1, $form->getData()->groups[0]->rules);
        }
    }

    private function createFormFactory(): DatagridFormFactory
    {
        return new DatagridFormFactory(DatagridNarrowingResolverFactory::createFormFactory());
    }

    private function createFilters(): FilterCollection
    {
        $translatorStub = $this->createStub(Translator::class);
        $translatorStub->method('trans')->willReturnArgument(0);
        Translator::injectSelf($translatorStub);

        $filter = TextFilter::new('name');
        $filter->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        ));

        return new FilterCollection()->add($filter);
    }
}
