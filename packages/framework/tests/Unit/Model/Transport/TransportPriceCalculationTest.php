<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleProvider;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceFactory;
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
        $customerUserRoleProviderMock = $this->getMockBuilder(CustomerUserRoleProvider::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['canCurrentCustomerUserSeePrices'])
            ->getMock();

        $customerUserRoleProviderMock
            ->expects($this->any())->method('canCurrentCustomerUserSeePrices')
                ->willReturn(true);

        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $transportPriceCalculation = new TransportPriceCalculation($basePriceCalculation, $pricingSettingMock, $customerUserRoleProviderMock);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);
        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->enabled = [Domain::FIRST_DOMAIN_ID => true];
        $transportData->vatsIndexedByDomainId = [
            Domain::FIRST_DOMAIN_ID => $vat,
        ];
        $transport = new Transport($transportData);
        $transport->setPrice($inputPrice, Domain::FIRST_DOMAIN_ID);
        $transport->addPrice(
            (new TransportPriceFactory(new EntityNameResolver([])))->create(
                $transport,
                $inputPrice,
                Domain::FIRST_DOMAIN_ID,
            ),
        );

        $price = $transportPriceCalculation->calculateIndependentPrice($transport, $currency, Domain::FIRST_DOMAIN_ID);

        $this->assertThat($price->getPriceWithoutVat(), new IsMoneyEqual($priceWithoutVat));
        $this->assertThat($price->getPriceWithVat(), new IsMoneyEqual($priceWithVat));
    }
}
