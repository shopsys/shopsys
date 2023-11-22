<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Product\Pricing;

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
     * @param int $inputPriceType
     * @param \Shopsys\FrameworkBundle\Model\Product\Product[] $variants
     * @return \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
     */
    private function getProductPriceCalculationWithInputPriceTypeAndVariants(int $inputPriceType, array $variants): \Shopsys\FrameworkBundle\Model\Product\Pricing\ProductPriceCalculation
    {
        $pricingSettingMock = $this->getMockBuilder(PricingSetting::class)
            ->setMethods(['getInputPriceType', 'getRoundingType', 'getDomainDefaultCurrencyIdByDomainId'])
            ->disableOriginalConstructor()
            ->getMock();
        $pricingSettingMock
            ->expects($this->any())->method('getInputPriceType')
                ->willReturn($inputPriceType);
        $pricingSettingMock
            ->expects($this->any())->method('getDomainDefaultCurrencyIdByDomainId')
                ->willReturn(1);

        $productManualInputPriceRepositoryMock = $this->getMockBuilder(ProductManualInputPriceRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $productRepositoryMock = $this->getMockBuilder(ProductRepository::class)
            ->setMethods(['getAllSellableVariantsByMainVariant'])
            ->disableOriginalConstructor()
            ->getMock();
        $productRepositoryMock
            ->expects($this->any())->method('getAllSellableVariantsByMainVariant')
            ->willReturn($variants);

        $currencyFacadeMock = $this->getMockBuilder(CurrencyFacade::class)
            ->disableOriginalConstructor()
            ->getMock();

        $rounding = new Rounding();
        $priceCalculation = new PriceCalculation($rounding);
        $basePriceCalculation = new BasePriceCalculation($priceCalculation, $rounding);

        return new ProductPriceCalculation(
            $basePriceCalculation,
            $pricingSettingMock,
            $productManualInputPriceRepositoryMock,
            $productRepositoryMock,
            $currencyFacadeMock,
        );
    }

    public function testCalculatePriceOfMainVariantWithoutAnySellableVariants(): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
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
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        $productPriceCalculation->getMinimumPriceByPriceWithoutVat([]);
    }

    /**
     * @dataProvider getMinimumPriceProvider
     * @param mixed[] $prices
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $minimumPrice
     */
    public function testGetMinimumPrice(array $prices, \Shopsys\FrameworkBundle\Model\Pricing\Price $minimumPrice): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->assertEquals($minimumPrice, $productPriceCalculation->getMinimumPriceByPriceWithoutVat($prices));
    }

    /**
     * @return array<int, array<'minimumPrice'|'prices', \Shopsys\FrameworkBundle\Model\Pricing\Price|\Shopsys\FrameworkBundle\Model\Pricing\Price[]>>
     */
    public function getMinimumPriceProvider(): array
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

    /**
     * @dataProvider getArePricesDifferentProvider
     * @param mixed[] $prices
     * @param bool $arePricesDifferent
     */
    public function testArePricesDifferent(array $prices, bool $arePricesDifferent): void
    {
        $productPriceCalculation = $this->getProductPriceCalculationWithInputPriceTypeAndVariants(
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->assertSame($arePricesDifferent, $productPriceCalculation->arePricesDifferent($prices));
    }

    /**
     * @return array<int, array<'arePricesDifferent'|'prices', bool|\Shopsys\FrameworkBundle\Model\Pricing\Price[]>>
     */
    public function getArePricesDifferentProvider(): array
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
            PricingSetting::INPUT_PRICE_TYPE_WITHOUT_VAT,
            [],
        );

        $this->expectException(InvalidArgumentException::class);
        $productPriceCalculation->arePricesDifferent([]);
    }
}
