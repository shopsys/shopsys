<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Tests\ShopBundle\Acceptance\acceptance\PageObject\AbstractPage;

class CartBoxPage extends AbstractPage
{
    /**
     * @deprecated test is deprecated and will be removed in the next major
     *
     * @param int $expectedCount
     * @param string $expectedPrice
     */
    public function seeCountAndPriceInCartBox(int $expectedCount, string $expectedPrice): void
    {
        $convertedPrice = Money::create($this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice));
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolOnFrontend($convertedPrice);
        $messageId = '{1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>';
        $translatedMessageWithTags = tc($messageId, $expectedCount, ['%itemsCount%' => $expectedCount, '%priceWithVat%' => $expectedFormattedPriceWithCurrency], 'messages', $this->tester->getFrontendLocale());

        $this->tester->seeInCss(strip_tags($translatedMessageWithTags), '.js-cart-info');
    }

    /**
     * @param int $expectedCount
     * @param string $expectedPrice
     */
    public function seeCountAndPriceRoundedByCurrencyInCartBox(int $expectedCount, string $expectedPrice): void
    {
        $convertedPrice = Money::create($this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice));
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend($convertedPrice);
        $messageId = '{1} <strong class="cart__state">%itemsCount%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%itemsCount%</strong> items for <strong class="cart__state">%priceWithVat%</strong>';
        $translatedMessageWithTags = tc($messageId, $expectedCount, ['%itemsCount%' => $expectedCount, '%priceWithVat%' => $expectedFormattedPriceWithCurrency], 'messages', $this->tester->getFrontendLocale());

        $this->tester->seeInCss(strip_tags($translatedMessageWithTags), '.js-cart-info');
    }
}
