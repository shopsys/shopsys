<?php

declare(strict_types=1);

namespace Tests\App\Functional\Model\Product\Pricing;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\PricingGroupDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Tests\App\Test\TransactionFunctionalTestCase;

class ProductPriceSecondaryCurrencyTest extends TransactionFunctionalTestCase
{
    /**
     * @inject
     */
    private ProductPriceCalculation $productPriceCalculation;

    /**
     * @inject
     */
    private CurrentCurrencyProvider $currentCurrencyProvider;

    /**
     * @inject
     */
    private Rounding $rounding;

    public function testSecondaryCurrencyPriceIsCalculatedByExchangeRateFromTheDomainDefaultCurrency(): void
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);
        $pricingGroup = $this->getReferenceForDomain(
            PricingGroupDataFixture::PRICING_GROUP_ORDINARY,
            Domain::FIRST_DOMAIN_ID,
            PricingGroup::class,
        );

        $defaultCurrencyPrice = $this->productPriceCalculation->calculatePrice(
            $product,
            Domain::FIRST_DOMAIN_ID,
            $pricingGroup,
        );

        $defaultCurrency = $this->currencyFacade->getDomainDefaultCurrencyByDomainId(Domain::FIRST_DOMAIN_ID);
        $czkCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);

        self::assertSame($defaultCurrency->getCode(), $defaultCurrencyPrice->getPrice()->getCurrency()?->getCode());

        try {
            $this->currentCurrencyProvider->setCurrentCurrencyCode('CZK');
            $czkPrice = $this->productPriceCalculation->calculatePrice(
                $product,
                Domain::FIRST_DOMAIN_ID,
                $pricingGroup,
            );
        } finally {
            $this->currentCurrencyProvider->setCurrentCurrencyCode(null);
        }

        self::assertSame('CZK', $czkPrice->getPrice()->getCurrency()?->getCode());

        $exchangeRate = $this->currencyFacade->getExchangeRateForCurrencies($defaultCurrency, $czkCurrency);
        $expectedPriceWithVat = $this->rounding->roundPriceWithVat(
            $defaultCurrencyPrice->getPrice()->getPriceWithVat()->multiply((string)$exchangeRate),
            $czkCurrency->getRoundingType(),
        );

        self::assertThat(
            $czkPrice->getPrice()->getPriceWithVat()->getAmount(),
            self::equalTo($expectedPriceWithVat->getAmount()),
        );
    }
}
