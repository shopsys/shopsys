<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Acceptance\acceptance\PageObject\Admin\ProductAdvancedSearchPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class AdminProductAdvancedSearchCest
{
    public function testSearchByCatnum(
        AcceptanceTester $me,
        LoginPage $loginPage,
        ProductAdvancedSearchPage $productAdvancedSearchPage,
    ): void {
        $me->wantTo('search for product by catnum');
        $loginPage->loginAsAdmin();

        $productAdvancedSearchPage->search(ProductAdvancedSearchPage::SEARCH_SUBJECT_CATNUM, '9176544MG');

        $productAdvancedSearchPage->assertFoundProductByName('Aquila Aquagym non-carbonated spring water');
        $productAdvancedSearchPage->assertFoundProductCount(1);
    }
}
