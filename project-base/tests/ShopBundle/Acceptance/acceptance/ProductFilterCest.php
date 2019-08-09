<?php

declare(strict_types=1);

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
        $productListPage->assertProductsTotalCount(28);

        $productFilterPage->setMinimalPrice(10);
        $productListPage->assertProductsTotalCount(24);

        $productFilterPage->setMaximalPrice(1000);
        $productListPage->assertProductsTotalCount(23);

        $productFilterPage->filterByBrand('LG');
        $productListPage->assertProductsTotalCount(5);

        $productFilterPage->filterByBrand('Hyundai');
        $productListPage->assertProductsTotalCount(10);

        $productFilterPage->filterByParameter('HDMI', 'Yes');
        $productListPage->assertProductsTotalCount(6);

        $productFilterPage->filterByParameter('Screen size', '27"');
        $productListPage->assertProductsTotalCount(2);

        $productFilterPage->filterByParameter('Screen size', '30"');
        $productListPage->assertProductsTotalCount(4);
    }
}
