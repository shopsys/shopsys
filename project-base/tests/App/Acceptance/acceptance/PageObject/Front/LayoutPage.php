<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class LayoutPage extends AbstractPage
{
    public function openLoginPopup(): void
    {
        $this->tester->clickByCss('.test-login-link-desktop');
        $this->tester->waitForAjax();
        $this->tester->wait(1); // wait for Shopsys.window to show
    }

    public function clickOnRegistration(): void
    {
        $this->tester->clickByCss('.test-registration-link-desktop');
    }

    public function logout(): void
    {
        $this->tester->clickByCss('.test-logout-link-desktop');
        $this->tester->seeTranslationFrontend('Log in');
        $this->tester->seeCurrentPageEquals('/');
    }
}
