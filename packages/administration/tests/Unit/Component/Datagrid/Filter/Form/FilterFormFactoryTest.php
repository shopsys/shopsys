<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Filter\Form;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Expression\ExpressionOperatorEnum;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterCollection;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\FilterEnvironment;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\Form\FilterFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Filter\TextFilter;
use Shopsys\FrameworkBundle\Component\Grid\DataSourceInterface;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

final class FilterFormFactoryTest extends TestCase
{
    public function testComposedFilterIsSubmittedFromTheQuery(): void
    {
        $form = $this->createFormFactory()->createForm($this->createFilters(), 'ProductReview_filter', [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]);

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

        foreach ([null, ['groups' => []]] as $query) {
            $form = $this->createFormFactory()->createForm($filters, 'ProductReview_filter', $query);

            $this->assertFalse($form->isSubmitted());
            $this->assertCount(1, $form->getData()->groups);
            $this->assertCount(1, $form->getData()->groups[0]->rules);
        }
    }

    /**
     * The page swaps the prototypes into the filter form, so they are named as that form.
     */
    public function testPrototypesAreNamedAsTheFilterForm(): void
    {
        $form = $this->createFormFactory()->createPrototypesForm($this->createFilters(), 'ProductReview_filter');

        $this->assertSame('ProductReview_filter', $form->getName());
        $this->assertFalse($form->isSubmitted());
    }

    private function createFormFactory(): FilterFormFactory
    {
        return new FilterFormFactory(DatagridStateFactoryFixture::createFormFactory());
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
