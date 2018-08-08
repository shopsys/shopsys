<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class LayoutPage extends AbstractPage
{
    public function openLoginPopup(): void
    {
        $this->tester->clickByCss('.js-login-link-desktop');
        $this->tester->wait(1); // wait for Shopsys.window to show
    }

    public function clickOnRegistration(): void
    {
        $this->tester->clickByCss('.js-registration-link-desktop');
    }

    public function logout(): void
    {
        $this->tester->clickByCss('.js-logout-link-desktop');
    }
}
