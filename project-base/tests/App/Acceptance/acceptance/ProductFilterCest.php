<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\ProductFilterPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\ProductListPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class ProductFilterCest
{
    private const BRAND_LG_POSITION = 3;
    private const BRAND_HYUNDAI_POSITION = 2;

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

        $productFilterPage->filterByBrand(static::BRAND_LG_POSITION);
        $productListPage->assertProductsTotalCount(3);

        $productFilterPage->filterByBrand(static::BRAND_HYUNDAI_POSITION);
        $productListPage->assertProductsTotalCount(7);

        $productFilterPage->filterByParameter('HDMI', 'Yes');
        $productListPage->assertProductsTotalCount(6);

        $productFilterPage->filterByParameter('Screen size', '27"');
        $productListPage->assertProductsTotalCount(2);

        $productFilterPage->filterByParameter('Screen size', '30"');
        $productListPage->assertProductsTotalCount(4);
    }
}
