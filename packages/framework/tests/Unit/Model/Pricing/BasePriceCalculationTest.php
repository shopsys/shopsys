<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatData;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class BasePriceCalculationTest extends TestCase
{
    /**
     * @return array<int, array<'basePriceVatAmount'|'basePriceWithoutVat'|'basePriceWithVat'|'inputPrice'|'inputPriceType'|'vatPercent', \Shopsys\FrameworkBundle\Component\Money\Money|int|'21'>>
     */
    public function calculateBasePriceProvider(): array
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
     * @dataProvider calculateBasePriceProvider
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $inputPrice
     * @param string $vatPercent
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithoutVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceWithVat
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $basePriceVatAmount
     */
    public function testCalculateBasePriceRoundedByCurrency(
        int $inputPriceType,
        Money $inputPrice,
        string $vatPercent,
        Money $basePriceWithoutVat,
        Money $basePriceWithVat,
        Money $basePriceVatAmount,
    ): void {
        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        $vatData = new VatData();
        $vatData->name = 'vat';
        $vatData->percent = $vatPercent;
        $vat = new Vat($vatData, Domain::FIRST_DOMAIN_ID);

        $currencyData = new CurrencyData();
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $currencyData->name = 'currency name';
        $currencyData->code = 'currency code';
        $currency = new Currency($currencyData);

        $basePrice = $basePriceCalculation->calculateBasePriceRoundedByCurrency(
            $inputPrice,
            $inputPriceType,
            $vat,
            $currency,
        );

        $this->assertThat($basePrice->getPriceWithoutVat(), new IsMoneyEqual($basePriceWithoutVat));
        $this->assertThat($basePrice->getPriceWithVat(), new IsMoneyEqual($basePriceWithVat));
        $this->assertThat($basePrice->getVatAmount(), new IsMoneyEqual($basePriceVatAmount));
    }
}
