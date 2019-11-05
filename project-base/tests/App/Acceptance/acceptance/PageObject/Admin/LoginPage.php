<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Admin;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class LoginPage extends AbstractPage
{
    public const ADMIN_USERNAME = 'admin';
    public const ADMIN_PASSWORD = 'admin123';

    /**
     * @param string $username
     * @param string $password
     */
    public function login($username, $password)
    {
        $this->tester->amOnPage('/admin/');
        $this->tester->fillFieldByName('admin_login_form[username]', $username);
        $this->tester->fillFieldByName('admin_login_form[password]', $password);
        $this->tester->clickByTranslationAdmin('Log in');
    }

    public function loginAsAdmin()
    {
        $this->login(self::ADMIN_USERNAME, self::ADMIN_PASSWORD);
        $this->tester->seeTranslationAdmin('Dashboard');
    }

    public function assertLoginFailed()
    {
        $this->tester->seeTranslationAdmin('Log in failed.');
        $this->tester->seeCurrentPageEquals('/admin/');
    }
}
