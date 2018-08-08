<?php

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class CartBoxPage extends AbstractPage
{
    public function seeInCartBox(string $text): void
    {
        $this->tester->seeInCss($text, '.js-cart-info');
    }
}
