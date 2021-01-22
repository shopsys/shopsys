<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class LayoutPage extends AbstractPage
{
    public function openLoginPopup()
    {
        $this->tester->clickByCss('.test-login-link-desktop');
        $this->tester->waitForAjax();
        // wait for Shopsys.window to show
        $this->tester->wait(1);
    }

    public function clickOnRegistration()
    {
        $this->tester->clickByCss('.test-registration-link-desktop');
    }

    public function logout()
    {
        $this->tester->clickByCss('.test-logout-link-desktop');
        $this->tester->seeTranslationFrontend('Log in');
        $this->tester->seeCurrentPageEquals('/');
    }
}
