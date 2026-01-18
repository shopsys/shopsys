<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Pricing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\InputPriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class InputPriceCalculationTest extends TestCase
{
    #[DataProvider('getInputPriceDataProvider')]
    public function testGetInputPrice(
        int $inputPriceType,
        Money $priceWithVat,
        string $vatPercent,
        Money $expectedResult,
    ): void {
        $inputPriceCalculation = new InputPriceCalculation();
        $actualInputPrice = $inputPriceCalculation->getInputPrice($inputPriceType, $priceWithVat, $vatPercent);

        $this->assertThat($actualInputPrice, new IsMoneyEqual($expectedResult));
    }

    public static function getInputPriceDataProvider(): array
    {
        return [
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'priceWithVat' => Money::create(121),
                'vatPercent' => '21',
                'expectedResult' => Money::create(100),
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITH_VAT,
                'priceWithVat' => Money::create(121),
                'vatPercent' => '21',
                'expectedResult' => Money::create(121),
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'priceWithVat' => Money::create(100),
                'vatPercent' => '0',
                'expectedResult' => Money::create(100),
            ],
            [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'priceWithVat' => Money::create(100),
                'vatPercent' => '21',
                'expectedResult' => Money::create('82.644628'),
            ],
        ];
    }
}
