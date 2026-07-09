<?php

declare(strict_types=1);

namespace Tests\FrontendApiBundle\Functional\Settings;

use App\DataFixtures\Demo\CurrencyDataFixture;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Tests\FrontendApiBundle\Test\GraphQlTestCase;

class PricingSettingCurrencyTest extends GraphQlTestCase
{
    private const string PRICING_SETTINGS_QUERY = '
        query {
            settings {
                pricing {
                    defaultCurrencyCode
                    currentCurrencyCode
                    minimumFractionDigits
                    availableCurrencies {
                        code
                        name
                        minFractionDigits
                    }
                }
            }
        }
    ';

    public function testCurrentCurrencyFallsBackToDomainDefaultWithoutHeader(): void
    {
        $pricingData = $this->getPricingSettingsData();

        $defaultCurrencyCode = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDefaultCurrencyCode();

        self::assertSame($defaultCurrencyCode, $pricingData['defaultCurrencyCode']);
        self::assertSame($defaultCurrencyCode, $pricingData['currentCurrencyCode']);
    }

    public function testCurrentCurrencyIsResolvedFromHeader(): void
    {
        $this->setCurrencyHeader('CZK');

        $pricingData = $this->getPricingSettingsData();

        $czkCurrency = $this->getReference(CurrencyDataFixture::CURRENCY_CZK, Currency::class);
        $defaultCurrencyCode = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDefaultCurrencyCode();

        self::assertSame($defaultCurrencyCode, $pricingData['defaultCurrencyCode']);
        self::assertSame('CZK', $pricingData['currentCurrencyCode']);
        self::assertSame($czkCurrency->getMinFractionDigits(), $pricingData['minimumFractionDigits']);
    }

    public function testUnknownCurrencyHeaderFallsBackToDomainDefault(): void
    {
        $this->setCurrencyHeader('XXX');

        $pricingData = $this->getPricingSettingsData();

        $defaultCurrencyCode = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getDefaultCurrencyCode();

        self::assertSame($defaultCurrencyCode, $pricingData['currentCurrencyCode']);
    }

    public function testAvailableCurrenciesMatchDomainConfigurationOrder(): void
    {
        $pricingData = $this->getPricingSettingsData();

        $expectedCurrencyCodes = $this->domain->getDomainConfigById(Domain::FIRST_DOMAIN_ID)->getCurrencyCodes();

        self::assertSame($expectedCurrencyCodes, array_column($pricingData['availableCurrencies'], 'code'));
    }

    /**
     * @return array<string, mixed>
     */
    private function getPricingSettingsData(): array
    {
        $response = $this->getResponseContentForQuery(self::PRICING_SETTINGS_QUERY);
        $data = $this->getResponseDataForGraphQlType($response, 'settings');

        return $data['pricing'];
    }

    private function setCurrencyHeader(string $currencyCode): void
    {
        $this->configureCurrentClient(null, null, [
            'CONTENT_TYPE' => 'application/graphql',
            'HTTP_X_CURRENCY_CODE' => $currencyCode,
        ]);
    }
}
