<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LayoutPage;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LoginPage;
use Tests\ShopBundle\Test\Codeception\AcceptanceTester;

class CustomerLoginCest
{
    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromMainPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage
    ) {
        $me->wantTo('login as a customer from main page');
        $me->amOnPage('/');
        $layoutPage->openLoginPopup();
        $loginPage->login('no-reply@shopsys.com', 'user123');
        $me->see('Jaromír Jágr');
        $layoutPage->logout();
        $me->seeTranslationFrontend('Log in');
        $me->seeCurrentPageEquals('/');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromCategoryPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage
    ) {
        $me->wantTo('login as a customer from category page');
        // personal-computers-accessories
        $me->amOnLocalizedRoute('front_product_list', ['id' => 6]);
        $layoutPage->openLoginPopup();
        $loginPage->login('no-reply@shopsys.com', 'user123');
        $me->see('Jaromír Jágr');
        $layoutPage->logout();
        $me->seeTranslationFrontend('Log in');
        $me->seeCurrentPageEquals('/');
    }

    /**
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\ShopBundle\Test\Codeception\AcceptanceTester $me
     * @param \Tests\ShopBundle\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromLoginPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage
    ) {
        $me->wantTo('login as a customer from login page');
        $me->amOnLocalizedRoute('front_login');
        $loginPage->login('no-reply@shopsys.com', 'user123');
        $me->see('Jaromír Jágr');
        $layoutPage->logout();
        $me->seeTranslationFrontend('Log in');
        $me->seeCurrentPageEquals('/');
    }
}
