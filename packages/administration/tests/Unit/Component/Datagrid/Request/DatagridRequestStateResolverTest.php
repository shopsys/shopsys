<?php

declare(strict_types=1);

namespace Tests\AdministrationBundle\Unit\Component\Datagrid\Request;

use PHPUnit\Framework\TestCase;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestState;
use Shopsys\AdministrationBundle\Component\Datagrid\Request\DatagridRequestStateResolver;
use Symfony\Component\Form\Forms;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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

        return (new DatagridRequestStateResolver(Forms::createFormFactory(), $requestStack))->resolve($gridId);
    }
}
