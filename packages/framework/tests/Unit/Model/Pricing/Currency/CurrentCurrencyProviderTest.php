<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing\Currency;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Setting\Setting;
use Shopsys\FrameworkBundle\Model\Administrator\CurrentAdministrator;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrentCurrencyProvider;
use Tests\FrameworkBundle\Test\DomainConfigHelper;

class CurrentCurrencyProviderTest extends TestCase
{
    private const int DOMAIN_ID = 1;

    /**
     * @param string[] $domainCurrencyCodes
     */
    private function createCurrentCurrencyProvider(array $domainCurrencyCodes): CurrentCurrencyProvider
    {
        $domainConfig = DomainConfigHelper::getDomainConfig(
            id: self::DOMAIN_ID,
            currencyCodes: $domainCurrencyCodes,
        );

        $domain = new Domain(
            [$domainConfig],
            $this->createStub(Setting::class),
            $this->createStub(CurrentAdministrator::class),
        );
        $domain->switchDomainById(self::DOMAIN_ID);

        $currencyFacadeStub = $this->createStub(CurrencyFacade::class);
        $currencyFacadeStub
            ->method('getByCode')
            ->willReturnCallback(fn (string $code): Currency => $this->createCurrency($code));

        return new CurrentCurrencyProvider($currencyFacadeStub, $domain);
    }

    public function testDomainDefaultCurrencyIsReturnedWhenNoCurrencyIsSet(): void
    {
        $currentCurrencyProvider = $this->createCurrentCurrencyProvider([Currency::CODE_EUR, Currency::CODE_CZK]);

        $this->assertSame(Currency::CODE_EUR, $currentCurrencyProvider->getCurrentCurrencyOfDomain(self::DOMAIN_ID)->getCode());
    }

    public function testSetCurrencyIsReturnedWhenEnabledOnDomain(): void
    {
        $currentCurrencyProvider = $this->createCurrentCurrencyProvider([Currency::CODE_EUR, Currency::CODE_CZK]);
        $currentCurrencyProvider->setCurrentCurrencyCode(Currency::CODE_CZK);

        $this->assertSame(Currency::CODE_CZK, $currentCurrencyProvider->getCurrentCurrencyOfDomain(self::DOMAIN_ID)->getCode());
        $this->assertSame(Currency::CODE_CZK, $currentCurrencyProvider->getCurrentCurrencyOfCurrentDomain()->getCode());
    }

    public function testDomainDefaultCurrencyIsReturnedWhenSetCurrencyIsNotEnabledOnDomain(): void
    {
        $currentCurrencyProvider = $this->createCurrentCurrencyProvider([Currency::CODE_EUR]);
        $currentCurrencyProvider->setCurrentCurrencyCode(Currency::CODE_CZK);

        $this->assertSame(Currency::CODE_EUR, $currentCurrencyProvider->getCurrentCurrencyOfDomain(self::DOMAIN_ID)->getCode());
    }

    public function testSettingNullResetsToDomainDefaultCurrency(): void
    {
        $currentCurrencyProvider = $this->createCurrentCurrencyProvider([Currency::CODE_EUR, Currency::CODE_CZK]);
        $currentCurrencyProvider->setCurrentCurrencyCode(Currency::CODE_CZK);
        $currentCurrencyProvider->setCurrentCurrencyCode(null);

        $this->assertNull($currentCurrencyProvider->getCurrentCurrencyCode());
        $this->assertSame(Currency::CODE_EUR, $currentCurrencyProvider->getCurrentCurrencyOfDomain(self::DOMAIN_ID)->getCode());
    }

    public function testResetClearsSetCurrency(): void
    {
        $currentCurrencyProvider = $this->createCurrentCurrencyProvider([Currency::CODE_EUR, Currency::CODE_CZK]);
        $currentCurrencyProvider->setCurrentCurrencyCode(Currency::CODE_CZK);
        $currentCurrencyProvider->reset();

        $this->assertNull($currentCurrencyProvider->getCurrentCurrencyCode());
        $this->assertSame(Currency::CODE_EUR, $currentCurrencyProvider->getCurrentCurrencyOfDomain(self::DOMAIN_ID)->getCode());
    }

    private function createCurrency(string $code): Currency
    {
        $currencyData = new CurrencyData();
        $currencyData->name = $code;
        $currencyData->code = $code;
        $currencyData->exchangeRate = Currency::DEFAULT_EXCHANGE_RATE;
        $currencyData->minFractionDigits = Currency::DEFAULT_MIN_FRACTION_DIGITS;
        $currencyData->roundingType = Currency::DEFAULT_ROUNDING_TYPE;
        $currencyData->roundingPlacesPriceWithoutVat = Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT;

        return new Currency($currencyData);
    }
}
