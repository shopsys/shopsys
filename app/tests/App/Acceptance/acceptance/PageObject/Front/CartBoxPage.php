<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\App\Acceptance\acceptance\PageObject\AbstractPage;

class CartBoxPage extends AbstractPage
{
    /**
     * @param int $expectedCount
     * @param string $expectedPrice
     */
    public function seeCountAndPriceRoundedByCurrencyInCartBox(int $expectedCount, string $expectedPrice): void
    {
        $convertedPrice = Money::create(
            $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice)
        );
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(
            $convertedPrice
        );

        $this->tester->seeInCss((string)$expectedCount, '.js-cart-count');
        $this->tester->seeInCss($expectedFormattedPriceWithCurrency, '.js-cart-price-with-vat');
    }
}
