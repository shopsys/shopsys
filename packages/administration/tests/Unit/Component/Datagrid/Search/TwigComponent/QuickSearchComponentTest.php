<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search\TwigComponent;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearch;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearchFormFactory;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\TwigComponent\QuickSearchComponent;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

final class QuickSearchComponentTest extends TestCase
{
    /**
     * The form keeps what was searched for, so the administrator sees why the records are narrowed.
     */
    public function testFormIsNamedAfterTheDatagridAndFilledWithTheTerm(): void
    {
        $component = new QuickSearchComponent(new QuickSearchFormFactory(DatagridStateFactoryFixture::createFormFactory()));
        $component->quickSearch = new QuickSearch('ProductReview_search', 'hrnek', ['name' => 'Name']);

        $formView = $component->getFormView();

        $this->assertSame('ProductReview_search', $formView->vars['full_name']);
        $this->assertSame('hrnek', $formView->children['text']->vars['value']);
        $this->assertSame($formView, $component->getFormView());
    }
}
