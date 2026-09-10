<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\AdditionalService;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalService;
use Shopsys\FrameworkBundle\Model\AdditionalService\AdditionalServicePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class AdditionalServicePriceCalculationTest extends TestCase
{
    /**
     * @return array<string, array{inputPriceType: int, serviceInputPrice: \Shopsys\FrameworkBundle\Component\Money\Money, quantity: int, expectedTotalPriceWithoutVat: \Shopsys\FrameworkBundle\Component\Money\Money, expectedTotalPriceWithVat: \Shopsys\FrameworkBundle\Component\Money\Money}>
     */
    public static function calculateTotalPriceProvider(): array
    {
        return [
            'input price with VAT re-derives the total VAT from the multiplied gross like products do' => [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITH_VAT,
                'serviceInputPrice' => Money::create('99.99'),
                'quantity' => 3,
                'expectedTotalPriceWithoutVat' => Money::create('247.91'),
                'expectedTotalPriceWithVat' => Money::create('299.97'),
            ],
            'input price without VAT multiplies both sides of the unit price' => [
                'inputPriceType' => PricingSetting::PRICE_TYPE_WITHOUT_VAT,
                'serviceInputPrice' => Money::create('99.99'),
                'quantity' => 3,
                'expectedTotalPriceWithoutVat' => Money::create('299.97'),
                'expectedTotalPriceWithVat' => Money::create('362.97'),
            ],
        ];
    }

    #[DataProvider('calculateTotalPriceProvider')]
    public function testCalculateTotalPrice(
        int $inputPriceType,
        Money $serviceInputPrice,
        int $quantity,
        Money $expectedTotalPriceWithoutVat,
        Money $expectedTotalPriceWithVat,
    ): void {
        $additionalServicePriceCalculation = $this->createAdditionalServicePriceCalculation($inputPriceType);
        $additionalServiceStub = $this->createAdditionalServiceStub($serviceInputPrice);
        $productStub = $this->createStub(Product::class);

        $totalPrice = $additionalServicePriceCalculation->calculateTotalPrice(
            $additionalServiceStub,
            $productStub,
            1,
            $quantity,
        );

        self::assertThat($totalPrice->getPriceWithoutVat(), new IsMoneyEqual($expectedTotalPriceWithoutVat));
        self::assertThat($totalPrice->getPriceWithVat(), new IsMoneyEqual($expectedTotalPriceWithVat));
    }

    private function createAdditionalServicePriceCalculation(int $inputPriceType): AdditionalServicePriceCalculation
    {
        $pricingSettingStub = $this->createStub(PricingSetting::class);
        $pricingSettingStub->method('getInputPriceType')->willReturn($inputPriceType);

        $currencyStub = $this->createStub(Currency::class);
        $currencyStub->method('getRoundingType')->willReturn(Currency::ROUNDING_TYPE_HUNDREDTHS);
        $currencyStub->method('getRoundingPlacesPriceWithoutVat')->willReturn(2);

        $currencyFacadeStub = $this->createStub(CurrencyFacade::class);
        $currencyFacadeStub->method('getDomainDefaultCurrencyByDomainId')->willReturn($currencyStub);

        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);

        return new AdditionalServicePriceCalculation(
            new BasePriceCalculation($priceCalculation, $rounding),
            $priceCalculation,
            $pricingSettingStub,
            $currencyFacadeStub,
        );
    }

    private function createAdditionalServiceStub(Money $serviceInputPrice): AdditionalService
    {
        $vatStub = $this->createStub(Vat::class);
        $vatStub->method('getPercent')->willReturn('21');

        $additionalServiceStub = $this->createStub(AdditionalService::class);
        $additionalServiceStub->method('getPriceForDomain')->willReturn($serviceInputPrice);
        $additionalServiceStub->method('isProductVatRateUsed')->willReturn(false);
        $additionalServiceStub->method('getVatForDomain')->willReturn($vatStub);

        return $additionalServiceStub;
    }
}
