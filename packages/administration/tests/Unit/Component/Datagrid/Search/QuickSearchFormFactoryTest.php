<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Search;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Search\QuickSearchFormFactory;
use Tests\AdministrationBundle\Unit\Component\Datagrid\Fixture\DatagridStateFactoryFixture;

final class QuickSearchFormFactoryTest extends TestCase
{
    public function testTermIsReadTrimmed(): void
    {
        $this->assertSame('hrnek', $this->createFormFactory()->getTerm('ProductReview_search', ['text' => '  hrnek ']));
    }

    public function testBlankTermCountsAsNoSearch(): void
    {
        $this->assertNull($this->createFormFactory()->getTerm('ProductReview_search', ['text' => '   ']));
    }

    /**
     * A value the form cannot take is no search rather than an error of the page.
     */
    public function testTermOfInvalidShapeCountsAsNoSearch(): void
    {
        $this->assertNull($this->createFormFactory()->getTerm('ProductReview_search', ['text' => ['hrnek']]));
    }

    public function testFormIsFilledWithTheTerm(): void
    {
        $form = $this->createFormFactory()->createForm('ProductReview_search', 'hrnek');

        $this->assertSame('ProductReview_search', $form->getName());
        $this->assertFalse($form->isSubmitted());
        $this->assertSame('hrnek', $form->createView()->children['text']->vars['value']);
    }

    private function createFormFactory(): QuickSearchFormFactory
    {
        return new QuickSearchFormFactory(DatagridStateFactoryFixture::createFormFactory());
    }
}
