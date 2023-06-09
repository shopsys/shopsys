<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class CustomerLoginCest
{
    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromMainPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage,
    ) {
        $me->wantTo('login as a customer from main page');
        $me->amOnPage('/');
        $layoutPage->openLoginPopup();
        $me->wait(10);
        $loginPage->login();
        $loginPage->checkUserLogged();
        $layoutPage->logout();
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromCategoryPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage,
    ) {
        $me->wantTo('login as a customer from category page');
        // personal-computers-accessories
        $me->amOnLocalizedRoute('front_product_list', ['id' => 6]);
        $layoutPage->openLoginPopup();
        $me->wait(10);
        $loginPage->login();
        $loginPage->checkUserLogged();
        $layoutPage->logout();
    }

    /**
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage $loginPage
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LayoutPage $layoutPage
     */
    public function testLoginAsCustomerFromLoginPage(
        LoginPage $loginPage,
        AcceptanceTester $me,
        LayoutPage $layoutPage,
    ) {
        $me->wantTo('login as a customer from login page');
        $me->amOnLocalizedRoute('front_login');
        $loginPage->login();
        $loginPage->checkUserLogged();
        $layoutPage->logout();
    }
}
