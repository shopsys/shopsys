<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Admin;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class LoginPage extends AbstractPage
{
    const ADMIN_USERNAME = 'admin';
    const ADMIN_PASSWORD = 'admin123';
    
    public function login(string $username, string $password): void
    {
        $this->tester->amOnPage('/admin/');
        $this->tester->fillFieldByName('admin_login_form[username]', $username);
        $this->tester->fillFieldByName('admin_login_form[password]', $password);
        $this->tester->clickByText('Log in');
    }

    public function loginAsAdmin(): void
    {
        $this->login(self::ADMIN_USERNAME, self::ADMIN_PASSWORD);
        $this->tester->see('Dashboard');
    }

    public function assertLoginFailed(): void
    {
        $this->tester->see('Log in failed.');
        $this->tester->seeCurrentPageEquals('/admin/');
    }
}
