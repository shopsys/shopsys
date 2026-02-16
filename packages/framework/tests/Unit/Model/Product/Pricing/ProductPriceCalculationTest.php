<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\BasePriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Exception\InvalidArgumentException;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\PriceCalculation;
use Shopsys\FrameworkBundle\Model\Pricing\PricingSetting;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Product\Pricing\Exception\MainVariantPriceCalculationException;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductManualInputPriceRepository;
use Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Product\ProductRepository;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class ProductPriceCalculationTest extends TestCase
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     */
    private function getProductPriceCalculationWithInputPriceTypeAndVariants(
        int $inputPriceType,
        array $variants,
    ): ProductPriceCalculation {
        $pricingSettingStub = $this->createStub(PricingSetting::class);
        $pricingSettingStub
            ->method('getInputPriceType')
                ->willReturn($inputPriceType);
        $pricingSettingStub
            ->method('getDomainDefaultCurrencyIdByDomainId')
                ->willReturn(1);

        $productManualInputPriceRepositoryStub = $this->createStub(ProductManualInputPriceRepository::class);

        $productRepositoryStub = $this->createStub(ProductRepository::class);
        $productRepositoryStub
            ->method('getAllSellableVariantsByMainVariant')
            ->willReturn($variants);

        $currencyFacadeStub = $this->createStub(CurrencyFacade::class);

        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        return new ProductPriceCalculation(
            $basePriceCalculation,
            $pricingSettingStub,
            $productManualInputPriceRepositoryStub,
            $productRepositoryStub,
            $currencyFacadeStub,
        );
    }

    public function testCalculatePriceOfMainVariantWithoutAnySellableVariants(): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, 1);

        $variant = Product::create(TestProductProvider::getTestProductData());
        $product = Product::createMainVariant(TestProductProvider::getTestProductData(), [$variant]);

        $this->expectException(MainVariantPriceCalculationException::class);

        $productPriceCalculation->calculatePrice($product, $pricingGroup->getDomainId(), $pricingGroup);
    }

    public function testGetMinimumPriceEmptyArray(): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        $productPriceCalculation->getMinimumPriceByPriceWithoutVat([]);
    }

    #[DataProvider('getMinimumPriceProvider')]
    public function testGetMinimumPrice(array $prices, mixed $minimumPrice): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->assertEquals($minimumPrice, $productPriceCalculation->getMinimumPriceByPriceWithoutVat($prices));
    }

    public static function getMinimumPriceProvider(): array
    {
        return [
            [
                'prices' => [
                    new Price(Money::create(20), Money::create(30)),
                    new Price(Money::create(10), Money::create(15)),
                    new Price(Money::create(100), Money::create(120)),
                ],
                'minimumPrice' => new Price(Money::create(10), Money::create(15)),
            ],
            [
                'prices' => [
                    new Price(Money::create(10), Money::create(15)),
                ],
                'minimumPrice' => new Price(Money::create(10), Money::create(15)),
            ],
            [
                'prices' => [
                    new Price(Money::create(10), Money::create(15)),
                    new Price(Money::create(10), Money::create(15)),
                ],
                'minimumPrice' => new Price(Money::create(10), Money::create(15)),
            ],
        ];
    }

    #[DataProvider('getArePricesDifferentProvider')]
    public function testArePricesDifferent(array $prices, mixed $arePricesDifferent): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->assertSame($arePricesDifferent, $productPriceCalculation->arePricesDifferent($prices));
    }

    public static function getArePricesDifferentProvider(): array
    {
        return [
            [
                'prices' => [
                    new Price(Money::create(100), Money::create(120)),
                    new Price(Money::create(100), Money::create(120)),
                ],
                'arePricesDifferent' => false,
            ],
            [
                'prices' => [
                    new Price(Money::create(100), Money::create(120)),
                ],
                'arePricesDifferent' => false,
            ],
            [
                'prices' => [
                    new Price(Money::create(200), Money::create(240)),
                    new Price(Money::create(100), Money::create(120)),
                ],
                'arePricesDifferent' => true,
            ],
        ];
    }

    public function testArePricesDifferentEmptyArray(): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        $productPriceCalculation->arePricesDifferent([]);
    }
}
