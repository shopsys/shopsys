<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class LoginPage extends AbstractPage
{
    private const DEFAULT_USER_NAME = 'Jaromír Jágr';
    private const DEFAULT_USER_EMAIL = 'no-reply@shopsys.com';
    private const DEFAULT_USER_PASSWORD = 'user123';

    public function login(): void
    {
        $this->tester->fillFieldByName('front_login_form[email]', self::DEFAULT_USER_EMAIL);
        $this->tester->fillFieldByName('front_login_form[password]', self::DEFAULT_USER_PASSWORD);
        $this->tester->clickByName('front_login_form[login]');
        $this->tester->waitForAjax();
    }

    /**
     * @param string|null $fullName
     */
    public function checkUserLogged(?string $fullName = null): void
    {
        $this->tester->see($fullName ?? self::DEFAULT_USER_NAME);
        $this->tester->seeTranslationFrontend('Log out');
    }
}
