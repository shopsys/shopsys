<?php

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductFilterPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class ProductFilterCest
{
    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductFilterPage $productFilterPage
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     */
    public function testAllProductFilters(
        AcceptanceTester $me,
        ProductFilterPage $productFilterPage,
        ProductListPage $productListPage
    ) {
        $me->wantTo('test all product filters');
        $me->amOnPage('/tv-audio/');
        $productListPage->assertProductsTotalCount(10);

        $productFilterPage->setMinimalPrice(1000);
        $productListPage->assertProductsTotalCount(5);

        $productFilterPage->setMaximalPrice(10000);
        $productListPage->assertProductsTotalCount(3);

        $productFilterPage->filterByBrand('Verbatim');
        $productListPage->assertProductsTotalCount(1);

        $productFilterPage->filterByBrand('Microsoft');
        $productListPage->assertProductsTotalCount(2);

        $productFilterPage->filterByParameter('Color', 'blue');
        $productListPage->assertProductsTotalCount(1);
    }
}
