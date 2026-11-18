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
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\CapturingAdapter;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

final class FilterFormFactoryTest extends TestCase
{
    use SetTranslatorTrait;

    public function testComposedFilterIsSubmittedFromTheQuery(): void
    {
        $form = $this->createFormFactory()->createForm($this->createFilters(), 'ProductReview_filter', [
            'groups' => [['rules' => [['filter' => 'name', 'operator' => 'contains', 'value' => 'x']]]],
        ]);

        $this->assertSame('ProductReview_filter', $form->getName());
        $this->assertTrue($form->isSubmitted());
        $this->assertSame('x', $form->getData()->groups[0]->rules[0]->value);
    }

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

    private function createFormFactory(): FilterFormFactory
    {
        return new FilterFormFactory(DatagridStateFactoryFixture::createFormFactory());
    }

    private function createFilters(): FilterCollection
    {
        $this->setTranslator();

        $filter = TextFilter::new('name');
        $filter->resolveFor(new FilterEnvironment(
            new CapturingAdapter($this->createStub(DataSourceInterface::class)),
            new ExpressionOperatorEnum(),
            new DateTimeZone('Europe/Prague'),
        ));

        return new FilterCollection()->add($filter);
    }
}
