<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\ProductFilterPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class ProductFilterCest
{
    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductFilterPage $productFilterPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage $productListPage
     */
    public function testAllProductFilters(
        AcceptanceTester $me,
        ProductFilterPage $productFilterPage,
        ProductListPage $productListPage
    ) {
        $me->wantTo('test all product filters');
        // tv-audio
        $me->amOnLocalizedRoute('front_product_list', ['id' => 3]);
        $productListPage->assertProductsTotalCount(28);

        $productFilterPage->setMinimalPrice(1000);
        $productListPage->assertProductsTotalCount(22);

        $productFilterPage->setMaximalPrice(10000);
        $productListPage->assertProductsTotalCount(16);

        $productFilterPage->filterByBrand('LG');
        $productListPage->assertProductsTotalCount(3);

        $productFilterPage->filterByBrand('Hyundai');
        $productListPage->assertProductsTotalCount(7);

        $productFilterPage->filterByParameter('HDMI', 'Yes');
        $productListPage->assertProductsTotalCount(6);

        $productFilterPage->filterByParameter('Screen size', '27"');
        $productListPage->assertProductsTotalCount(2);

        $productFilterPage->filterByParameter('Screen size', '30"');
        $productListPage->assertProductsTotalCount(4);
    }
}
