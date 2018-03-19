<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class LayoutPage extends AbstractPage
{
    public function openLoginPopup()
    {
        $this->tester->clickByCss('.js-login-link-desktop');
        $this->tester->wait(1); // wait for Shopsys.window to show
    }

    public function logout()
    {
        $this->tester->clickByCss('.js-logout-link-desktop');
    }
}
