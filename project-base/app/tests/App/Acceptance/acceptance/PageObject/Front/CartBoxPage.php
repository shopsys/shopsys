<?php

declare(strict_types=1);

namespace Tests\App\Acceptance\acceptance\PageObject\Front;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Translation\Translator;
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
            $this->tester->getPriceWithVatConvertedToDomainDefaultCurrency($expectedPrice),
        );
        $expectedFormattedPriceWithCurrency = $this->tester->getFormattedPriceWithCurrencySymbolRoundedByCurrencyOnFrontend(
            $convertedPrice,
        );
        $messageId = '{1} <strong class="cart__state">%count%</strong> item for <strong class="cart__state">%priceWithVat%</strong>|[2,Inf] <strong class="cart__state">%count%</strong> items for <strong class="cart__state">%priceWithVat%</strong>';
        $translatedMessageWithTags = t(
            $messageId,
            [
                '%count%' => $expectedCount,
                '%priceWithVat%' => $expectedFormattedPriceWithCurrency,
            ],
            Translator::DEFAULT_TRANSLATION_DOMAIN,
            $this->tester->getFrontendLocale(),
        );

        $this->tester->seeInCss(strip_tags($translatedMessageWithTags), '.test-cart-info');
    }
}
