<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Product;

use App\DataFixtures\Demo\CurrencyDataFixture;
use App\DataFixtures\Demo\ProductDataFixture;
use App\Model\Product\Product;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class ProductPriceCurrencyTest extends GraphQlTestCase
{
    private const string PRODUCT_PRICE_QUERY = '
        query ($uuid: Uuid!) {
            product(uuid: $uuid) {
                price {
                    priceWithVat
                    currencyCode
                }
            }
        }
    ';

    /**
     * @inject
     */
    private Rounding $rounding;

    public function testProductPriceIsReturnedInDomainDefaultCurrencyWithoutHeader(): void
    {
        $priceData = $this->getProductPriceData();

        $defaultCurrencyCode = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDefaultCurrencyCode();

        self::assertSame($defaultCurrencyCode, $priceData['currencyCode']);
    }

    public function testProductPriceIsConvertedToSecondaryCurrencyFromHeader(): void
    {
        $defaultCurrencyPriceData = $this->getProductPriceData();

        $this->setCurrencyHeader('CZK');
        $czkPriceData = $this->getProductPriceData();

        self::assertSame('CZK', $czkPriceData['currencyCode']);

        $czkCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $defaultCurrency = $this->getFirstDomainCurrency();

        $exchangeRate = $this->currencyFacade->getExchangeRateForCurrencies($defaultCurrency, $czkCurrency);

        $expectedPriceWithVat = $this->rounding->roundPriceWithVat(
            Money::create($defaultCurrencyPriceData['priceWithVat'])->multiply((string)$exchangeRate),
            $czkCurrency->getRoundingType(),
        );

        self::assertSame(
            $this->moneyFormatterHelper->formatWithMaxFractionDigits($expectedPriceWithVat),
            $czkPriceData['priceWithVat'],
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function getProductPriceData(): array
    {
        $product = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '1', Product::class);

        $response = $this->getResponseContentForQuery(self::PRODUCT_PRICE_QUERY, ['uuid' => $product->getUuid()]);
        $data = $this->getResponseDataForGraphQlType($response, 'product');

        return $data['price'];
    }

    private function setCurrencyHeader(string $currencyCode): void
    {
        $this->configureCurrentClient(null, null, [
            'CONTENT_TYPE' => 'application/graphql',
            'HTTP_X_CURRENCY_CODE' => $currencyCode,
        ]);
    }
}
