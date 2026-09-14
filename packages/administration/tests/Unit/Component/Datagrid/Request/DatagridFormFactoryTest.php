<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Request;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
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

    private function createFormFactory(): DatagridFormFactory
    {
        return new DatagridFormFactory(DatagridNarrowingResolverFactory::createFormFactory());
    }
}
