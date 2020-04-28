<?php

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class RoundingTest extends TestCase
{
    public function roundingProvider()
    {
        return [
            [
                'unroundedPrice' => Money::create(0),
                'expectedAsPriceWithVat' => Money::create(0),
                'expectedAsPriceWithoutVat' => Money::create(0),
                'expectedAsVatAmount' => Money::create(0),
            ],
            [
                'unroundedPrice' => Money::create(1),
                'expectedAsPriceWithVat' => Money::create(1),
                'expectedAsPriceWithoutVat' => Money::create(1),
                'expectedAsVatAmount' => Money::create(1),
            ],
            [
                'unroundedPrice' => Money::create('0.999'),
                'expectedAsPriceWithVat' => Money::create(1),
                'expectedAsPriceWithoutVat' => Money::create(1),
                'expectedAsVatAmount' => Money::create(1),
            ],
            [
                'unroundedPrice' => Money::create('0.99'),
                'expectedAsPriceWithVat' => Money::create(1),
                'expectedAsPriceWithoutVat' => Money::create('0.99'),
                'expectedAsVatAmount' => Money::create('0.99'),
            ],
            [
                'unroundedPrice' => Money::create('0.5'),
                'expectedAsPriceWithVat' => Money::create(1),
                'expectedAsPriceWithoutVat' => Money::create('0.50'),
                'expectedAsVatAmount' => Money::create('0.50'),
            ],
            [
                'unroundedPrice' => Money::create('0.49'),
                'expectedAsPriceWithVat' => Money::create(0),
                'expectedAsPriceWithoutVat' => Money::create('0.49'),
                'expectedAsVatAmount' => Money::create('0.49'),
            ],
        ];
    }

    /**
     * @dataProvider roundingProvider
     * @param mixed $unroundedPrice
     * @param mixed $expectedAsPriceWithVat
     * @param mixed $expectedAsPriceWithoutVat
     * @param mixed $expectedAsVatAmount
     */
    public function testRoundingByCurrency(
        $unroundedPrice,
        $expectedAsPriceWithVat,
        $expectedAsPriceWithoutVat,
        $expectedAsVatAmount
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->getMock();
        $rounding = new Rounding($pricingSettingMock);

        $currencyData = new CurrencyData();
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currency = new Currency($currencyData);

        $this->assertThat($rounding->roundPriceWithVatByCurrency($unroundedPrice, $currency), new IsMoneyEqual($expectedAsPriceWithVat));
        $this->assertThat($rounding->roundPriceWithoutVat($unroundedPrice), new IsMoneyEqual($expectedAsPriceWithoutVat));
        $this->assertThat($rounding->roundVatAmount($unroundedPrice), new IsMoneyEqual($expectedAsVatAmount));
    }

    public function roundingPriceWithVatProvider()
    {
        return [
            [
                'roundingType' => Currency::ROUNDING_TYPE_INTEGER,
                'inputPrice' => Money::create('1.5'),
                'outputPrice' => Money::create(2),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_INTEGER,
                'inputPrice' => Money::create('1.49'),
                'outputPrice' => Money::create(1),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_HUNDREDTHS,
                'inputPrice' => Money::create('1.01'),
                'outputPrice' => Money::create('1.01'),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_HUNDREDTHS,
                'inputPrice' => Money::create('1.009'),
                'outputPrice' => Money::create('1.01'),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_HUNDREDTHS,
                'inputPrice' => Money::create('1.001'),
                'outputPrice' => Money::create(1),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_FIFTIES,
                'inputPrice' => Money::create('1.24'),
                'outputPrice' => Money::create(1),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_FIFTIES,
                'inputPrice' => Money::create('1.25'),
                'outputPrice' => Money::create('1.5'),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_FIFTIES,
                'inputPrice' => Money::create('1.74'),
                'outputPrice' => Money::create('1.5'),
            ],
            [
                'roundingType' => Currency::ROUNDING_TYPE_FIFTIES,
                'inputPrice' => Money::create('1.75'),
                'outputPrice' => Money::create(2),
            ],
        ];
    }

    /**
     * @dataProvider roundingPriceWithVatProvider
     * @param mixed $roundingType
     * @param mixed $inputPrice
     * @param mixed $outputPrice
     */
    public function testRoundingPriceWithVatByCurrency(
        $roundingType,
        $inputPrice,
        $outputPrice
    ) {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->disableOriginalConstructor()
            ->getMock();

        $currencyData = new CurrencyData();
        $currencyData->roundingType = $roundingType;
        $currency = new Currency($currencyData);

        $rounding = new Rounding($pricingSettingMock);
        $roundedPrice = $rounding->roundPriceWithVatByCurrency($inputPrice, $currency);

        $this->assertThat($roundedPrice, new IsMoneyEqual($outputPrice));
    }
}
