<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleResolver;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\PriceWithLimitData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData;
use Shopsys\FrameworkBundle\Model\Transport\TransportPrice;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFacade;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class TransportPriceCalculationTest extends TestCase
{
    public static function calculateIndependentPriceProvider()
    {
        return [
            [
                'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
                'inputPrice' => Money::create(6999),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create('6999.17'),
                'priceWithVat' => Money::create(8469),
            ],
            [
                'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
                'inputPrice' => Money::create('6999.99'),
                'vatPercent' => '21',
                'priceWithoutVat' => Money::create('5785.12'),
                'priceWithVat' => Money::create(7000),
            ],
        ];
    }

    /**
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param string $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     */
    #[DataProvider('calculateIndependentPriceProvider')]
    public function testCalculateIndependentPrice(
        int $inputPriceType,
        Money $inputPrice,
        string $vatPercent,
        Money $priceWithoutVat,
        Money $priceWithVat,
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->onlyMethods(['getInputPriceType'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingSettingMock
            ->expects($this->any())->method('getInputPriceType')
                ->willReturn($inputPriceType);
        $customerUserRoleResolverMock = $this->getMockBuilder(CustomerUserRoleResolver::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canCurrentCustomerUserSeePrices'])
            ->getMock();
        $customerUserRoleResolverMock
            ->expects($this->any())->method('canCurrentCustomerUserSeePrices')
                ->willReturn(true);

        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);
        $currencyFacadeMock = $this->createMock(CurrencyFacade::class);
        $currencyData = new CurrencyData();
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currencyFacadeMock
            ->expects($this->any())->method('getDomainDefaultCurrencyByDomainId')
            ->willReturn(new Currency($currencyData));
        $transportPriceFacadeMock = $this->createMock(TransportPriceFacade::class);

        $transportPriceCalculation = new TransportPriceCalculation($basePriceCalculation, $pricingSettingMock, $customerUserRoleResolverMock, $currencyFacadeMock, $transportPriceFacadeMock);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);

        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->enabled = [Domain::FIRST_DOMAIN_ID => true];
        $transportInputPricesData = new TransportInputPricesData();
        $transportInputPricesData->vat = $vat;
        $priceWithLimitData = new PriceWithLimitData();
        $priceWithLimitData->price = $inputPrice;
        $transportInputPricesData->pricesWithLimits = [$priceWithLimitData];
        $transportData->inputPricesByDomain = [Domain::FIRST_DOMAIN_ID => $transportInputPricesData];
        $transport = new Transport($transportData);
        $transportPrice = new TransportPrice($transport, $inputPrice, Domain::FIRST_DOMAIN_ID, null);

        $price = $transportPriceCalculation->calculateIndependentPrice($transportPrice);

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual($priceWithoutVat));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual($priceWithVat));
    }
}
