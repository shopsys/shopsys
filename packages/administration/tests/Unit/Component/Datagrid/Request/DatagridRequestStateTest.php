<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Request;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Symfony\Component\HttpFoundation\Request;

final class DatagridRequestStateTest extends TestCase
{
    public function testQueryIsReadFromTheFormsNamedAfterTheDatagrid(): void
    {
        $state = DatagridRequestState::fromRequest(new Request([
            'ProductReview_search' => [
                'text' => 'hrnek',
            ],
        ]), 'ProductReview');

        $this->assertTrue($state->inRequest);
        $this->assertSame('ProductReview', $state->gridId);
        $this->assertSame(['text' => 'hrnek'], $state->quickSearchQuery);
    }

    public function testNothingIsReadWithoutTheParameters(): void
    {
        $state = DatagridRequestState::fromRequest(new Request([
            'g' => ['ProductReview' => ['page' => '2']],
        ]), 'ProductReview');

        $this->assertTrue($state->inRequest, 'The forms are built anyway, so that the components can render them.');
        $this->assertNull($state->quickSearchQuery);
    }

    public function testTheFormsOfAnotherDatagridAreNotRead(): void
    {
        $state = DatagridRequestState::fromRequest(new Request([
            'ProductReview_search' => ['text' => 'hrnek'],
        ]), 'Order');

        $this->assertNull($state->quickSearchQuery);
    }

    public function testStateOutsideRequestBuildsNoForm(): void
    {
        $state = DatagridRequestState::outsideRequest('ProductReview');

        $this->assertFalse($state->inRequest);
        $this->assertNull($state->quickSearchQuery);
    }

    /**
     * A form name allows only letters, digits, underscores, hyphens and colons; the ID of a datagrid is free text.
     */
    public function testFormNamesAreSafeForAnyDatagridId(): void
    {
        $state = new DatagridRequestState('Product review');

        $this->assertSame('Product_review_search', $state->getQuickSearchFormName());
    }
}
