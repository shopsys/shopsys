<?php

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class BasePriceCalculationTest extends TestCase
{
    public function calculateBasePriceProvider()
    {
        return [
            [
                'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
                'inputPrice' => Money::create(6999),
                'vatPercent' => '21',
                'basePriceWithoutVat' => Money::create('6999.17'),
                'basePriceWithVat' => Money::create(8469),
                'basePriceVatAmount' => Money::create('1469.83'),
            ],
            [
                'inputPriceType' => PricingSetting::INPUT_PRICE_TYPE_WITH_VAT,
                'inputPrice' => Money::create('6999.99'),
                'vatPercent' => '21',
                'basePriceWithoutVat' => Money::create('5785.12'),
                'basePriceWithVat' => Money::create(7000),
                'basePriceVatAmount' => Money::create('1214.88'),
            ],
        ];
    }

    /**
     * @deprecated test is deprecated and will be removed in the next major
     *
     * @dataProvider calculateBasePriceProvider
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param mixed $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceVatAmount
     */
    public function testCalculateBasePrice(
        int $inputPriceType,
        Money $inputPrice,
        $vatPercent,
        Money $basePriceWithoutVat,
        Money $basePriceWithVat,
        Money $basePriceVatAmount
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->setMethods(['getRoundingType'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingSettingMock
            ->expects($this->any())->method('getRoundingType')
                ->willReturn(PricingSetting::ROUNDING_TYPE_INTEGER);

        $rounding = new Rounding($pricingSettingMock);
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData);

        $basePrice = $basePriceCalculation->calculateBasePrice($inputPrice, $inputPriceType, $vat);

        $this->assertThat($basePrice->getPriceWithoutVat(), new IsMoneyEqual($basePriceWithoutVat));
        $this->assertThat($basePrice->getPriceWithVat(), new IsMoneyEqual($basePriceWithVat));
        $this->assertThat($basePrice->getVatAmount(), new IsMoneyEqual($basePriceVatAmount));
    }

    /**
     * @dataProvider calculateBasePriceProvider
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param mixed $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceVatAmount
     */
    public function testCalculateBasePriceRoundedByCurrency(
        int $inputPriceType,
        Money $inputPrice,
        $vatPercent,
        Money $basePriceWithoutVat,
        Money $basePriceWithVat,
        Money $basePriceVatAmount
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rounding = new Rounding($pricingSettingMock);
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData);

        $currencyData = new CurrencyData();
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $basePrice = $basePriceCalculation->calculateBasePriceRoundedByCurrency($inputPrice, $inputPriceType, $vat, $currency);

        $this->assertThat($basePrice->getPriceWithoutVat(), new IsMoneyEqual($basePriceWithoutVat));
        $this->assertThat($basePrice->getPriceWithVat(), new IsMoneyEqual($basePriceWithVat));
        $this->assertThat($basePrice->getVatAmount(), new IsMoneyEqual($basePriceVatAmount));
    }

    /**
     * @deprecated provider is deprecated and will be removed in the next major.
     * Test uses this provider works with unused function
     */
    public function applyCoefficientProvider()
    {
        return [
            [
                'priceWithVat' => Money::create(100),
                'vatPercent' => '20',
                'coefficients' => ['2'],
                'resultPriceWithVat' => Money::create(200),
                'resultPriceWithoutVat' => Money::create(167),
                'resultVatAmount' => Money::create(33),
            ],
            [
                'priceWithVat' => Money::create(100),
                'vatPercent' => '10',
                'coefficients' => ['1'],
                'resultPriceWithVat' => Money::create(100),
                'resultPriceWithoutVat' => Money::create(91),
                'resultVatAmount' => Money::create(9),
            ],
            [
                'priceWithVat' => Money::create(100),
                'vatPercent' => '20',
                'coefficients' => ['0.6789'],
                'resultPriceWithVat' => Money::create(68),
                'resultPriceWithoutVat' => Money::create(57),
                'resultVatAmount' => Money::create(11),
            ],
        ];
    }

    /**
     * @deprecated test is deprecated and will be removed in the next major. Test works with unused function
     *
     * @dataProvider applyCoefficientProvider
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $priceWithVat
     * @param mixed $vatPercent
     * @param mixed $coefficients
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $resultPriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $resultPriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $resultVatAmount
     */
    public function testApplyCoefficient(
        Money $priceWithVat,
        $vatPercent,
        $coefficients,
        Money $resultPriceWithVat,
        Money $resultPriceWithoutVat,
        Money $resultVatAmount
    ) {
        $rounding = $this->getMockBuilder(Rounding::class)
            ->setMethods(['roundPriceWithVat', 'roundPriceWithoutVat', 'roundVatAmount'])
            ->disableOriginalConstructor()
            ->getMock();
        $rounding->expects($this->any())->method('roundPriceWithVat')->willReturnCallback(function (Money $value) {
            return $value->round(0);
        });
        $rounding->expects($this->any())->method('roundPriceWithoutVat')->willReturnCallback(function (Money $value) {
            return $value->round(0);
        });
        $rounding->expects($this->any())->method('roundVatAmount')->willReturnCallback(function (Money $value) {
            return $value->round(0);
        });
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $price = new Price(Money::zero(), $priceWithVat);
        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData);
        $resultPrice = $basePriceCalculation->applyCoefficients($price, $vat, $coefficients);

        $this->assertThat($resultPrice->getPriceWithVat(), new IsMoneyEqual($resultPriceWithVat));
        $this->assertThat($resultPrice->getPriceWithoutVat(), new IsMoneyEqual($resultPriceWithoutVat));
        $this->assertThat($resultPrice->getVatAmount(), new IsMoneyEqual($resultVatAmount));
    }
}
