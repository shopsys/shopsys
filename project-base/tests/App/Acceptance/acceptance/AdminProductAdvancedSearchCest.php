<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Acceptance\acceptance\PageObject\Admin\ProductAdvancedSearchPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class AdminProductAdvancedSearchCest
{
    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\ProductAdvancedSearchPage $productAdvancedSearchPage
     */
    public function testSearchByCatnum(
        AcceptanceTester $me,
        LoginPage $loginPage,
        ProductAdvancedSearchPage $productAdvancedSearchPage
    ) {
        $me->wantTo('search for product by catnum');
        $loginPage->loginAsAdmin();

        $productAdvancedSearchPage->search(ProductAdvancedSearchPage::SEARCH_SUBJECT_CATNUM, '9176544MG');

        $productAdvancedSearchPage->assertFoundProductByName('Aquila Aquagym non-carbonated spring water');
        $productAdvancedSearchPage->assertFoundProductCount(1);
    }
}
