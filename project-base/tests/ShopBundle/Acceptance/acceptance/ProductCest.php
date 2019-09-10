<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class ProductCest
{
    protected const SAVE_BUTTON_NAME = 'product_form[save]';

    /**
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     */
    public function testProductCategoryAreVisibleAfterSave(AcceptanceTester $me, LoginPage $loginPage)
    {
        $me->wantTo('See product categories after save');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/product/new/');
        $me->clickByName(self::SAVE_BUTTON_NAME);
        $me->scrollTo(['css' => '.js-entity-url-list-domain-1'], null, -100);
        $me->see('Electronics', ['css' => '.js-entity-url-list-domain-1']);
    }
}
