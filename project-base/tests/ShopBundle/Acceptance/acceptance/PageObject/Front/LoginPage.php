<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class LoginPage extends AbstractPage
{
    public function login(string $email, string $password): void
    {
        $this->tester->fillFieldByName('front_login_form[email]', $email);
        $this->tester->fillFieldByName('front_login_form[password]', $password);
        $this->tester->clickByName('front_login_form[login]');
        $this->tester->waitForAjax();
    }
}
