<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance;

use Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage;
use Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage as FrontendLoginPage;
use Tests\App\Test\Codeception\AcceptanceTester;

class LoginAsCustomerCest
{
    /**
     * @param \Tests\App\Test\Codeception\AcceptanceTester $me
     * @param \Tests\App\Acceptance\acceptance\PageObject\Admin\LoginPage $loginPage
     * @param \Tests\App\Acceptance\acceptance\PageObject\Front\LoginPage $frontendLoginPage
     */
    public function testLoginAsCustomer(
        AcceptanceTester $me,
        LoginPage $loginPage,
        FrontendLoginPage $frontendLoginPage,
    ) {
        $me->wantTo('login as a customer from admin');
        $loginPage->loginAsAdmin();
        $me->amOnPage('/admin/customer/edit/2');
        $me->clickByTranslationAdmin('Log in as user');
        $me->switchToLastOpenedWindow();
        $me->seeCurrentPageEquals('/');
        $me->seeTranslationFrontend('Attention! You are administrator logged in as the customer.');
        $frontendLoginPage->checkUserLogged('Igor Anpilogov');
    }
}
