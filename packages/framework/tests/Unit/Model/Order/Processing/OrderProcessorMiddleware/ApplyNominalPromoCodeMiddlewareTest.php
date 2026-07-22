<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyNominalPromoCodeMiddleware;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\DiscountCalculation;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeLimit\PromoCodeLimit;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;
use Tests\FrameworkBundle\Test\SetTranslatorTrait;

class ApplyNominalPromoCodeMiddlewareTest extends MiddlewareTestCase
{
    use SetTranslatorTrait;

    public function testAddPromoCode(): void
    {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();

        $expectedPrice = new Price(Money::create(-100), Money::create(-121));

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL;
        $promoCode = new PromoCode($promoCodeData);

        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $applyNominalPromoCodeMiddleware = $this->createApplyNominalPromoCodeMiddleware($expectedPrice->inverse());

        $result = $applyNominalPromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]),
            new IsPriceEqual($expectedPrice),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedPrice),
        );

        $actualDiscountItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(1, $actualDiscountItemsType);
        $this->assertCount(1, $actualOrderData->items);

        $this->assertSame($actualDiscountItemsType[0]->promoCode, $promoCode);
        $this->assertSame('Promo code', $actualDiscountItemsType[0]->name);
    }

    #[DataProvider('invalidPromoCodeTypeDataProvider')]
    public function testNoPromoCodeIsAdded(?string $promoCodeType): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        if ($promoCodeType !== null) {
            $promoCodeData = new PromoCodeData();
            $promoCodeData->code = 'promoCode';
            $promoCodeData->discountType = $promoCodeType;
            $promoCode = new PromoCode($promoCodeData);

            $orderProcessingData->orderInput->addPromoCode($promoCode);
        }

        $applyNominalPromoCodeMiddleware = $this->createApplyNominalPromoCodeMiddleware(Price::zero());

        $result = $applyNominalPromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualOrderData = $result->orderData;

        $actualDiscountItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(0, $actualDiscountItemsType);
        $this->assertCount(0, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_DISCOUNT]),
            new IsPriceEqual(Price::zero()),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );
    }

    /**
     * @param string[]|null $discountableItemTypes
     */
    #[DataProvider('discountableItemTypesDataProvider')]
    public function testDiscountIsCalculatedFromDiscountableItemsOfValidProducts(
        ?array $discountableItemTypes,
        Price $expectedApplicablePrice,
    ): void {
        $this->setTranslator();

        $orderProcessingData = $this->createOrderProcessingData();
        $orderData = $orderProcessingData->orderData;

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL;
        $promoCode = new PromoCode($promoCodeData);

        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $productPrice = new Price(Money::create(1000), Money::create(1210));
        $productItemData = $this->addProductItemToOrderData($orderData, $productPrice, 1, 'product 1', 1);
        $orderData->addTotalPrice($productPrice, OrderItemTypeEnum::TYPE_PRODUCT);

        $additionalServiceItemData = $this->addAdditionalServiceItemToOrderData(
            $orderData,
            $productItemData,
            new Price(Money::create(100), Money::create(121)),
            'additional service',
        );

        $applyNominalPromoCodeMiddleware = $this->createApplyNominalPromoCodeMiddleware(
            new Price(Money::create(100), Money::create(121)),
            $expectedApplicablePrice,
            $discountableItemTypes,
        );

        $result = $applyNominalPromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $actualDiscountItems = $result->orderData->getItemsByType(OrderItemTypeEnum::TYPE_DISCOUNT);

        $this->assertCount(1, $actualDiscountItems);
        $this->assertContains($actualDiscountItems[0], $productItemData->relatedOrderItemsData);
        $this->assertSame([], $additionalServiceItemData->relatedOrderItemsData);
    }

    public static function discountableItemTypesDataProvider(): iterable
    {
        yield 'only products are discountable by default' => [
            null,
            new Price(Money::create(1000), Money::create(1210)),
        ];

        yield 'additional services are discountable when their type is included' => [
            [OrderItemTypeEnum::TYPE_PRODUCT, OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE],
            new Price(Money::create(1100), Money::create(1331)),
        ];
    }

    public static function invalidPromoCodeTypeDataProvider(): iterable
    {
        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT];

        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT];

        yield [null];
    }

    /**
     * @param string[]|null $discountableItemTypes
     */
    private function createApplyNominalPromoCodeMiddleware(
        ?Price $discountPrice,
        ?Price $expectedApplicablePrice = null,
        ?array $discountableItemTypes = null,
    ): ApplyNominalPromoCodeMiddleware {
        $currentPromoCodeFacade = $this->createStub(CurrentPromoCodeFacade::class);
        $currentPromoCodeFacade->method('validatePromoCode')->willReturn([1]);

        $promoCodeFacade = $this->createStub(PromoCodeFacade::class);
        $promoCodeFacade->method('getHighestLimitByPromoCodeAndTotalPrice')->willReturn(new PromoCodeLimit('1', '10'));

        if ($expectedApplicablePrice === null) {
            $discountCalculation = $this->createStub(DiscountCalculation::class);
            $discountCalculation->method('calculateNominalDiscount')->willReturn($discountPrice);
        } else {
            $discountCalculation = $this->createMock(DiscountCalculation::class);
            $discountCalculation->expects($this->once())
                ->method('calculateNominalDiscount')
                ->with($this->anything(), new IsPriceEqual($expectedApplicablePrice), $this->anything(), $this->anything())
                ->willReturn($discountPrice);
        }

        $vatFacade = $this->createStub(VatFacade::class);

        $middlewareDependencies = [
            $currentPromoCodeFacade,
            $promoCodeFacade,
            $discountCalculation,
            $this->createOrderItemDataFactory(),
            $vatFacade,
            $this->createCurrencyFacadeStub(),
        ];

        if ($discountableItemTypes === null) {
            return new ApplyNominalPromoCodeMiddleware(...$middlewareDependencies);
        }

        $applyNominalPromoCodeMiddleware = new class(...$middlewareDependencies) extends ApplyNominalPromoCodeMiddleware {
            /**
             * @var string[]
             */
            public array $discountableItemTypes = [];

            /**
             * {@inheritdoc}
             */
            #[Override]
            protected function getDiscountableItemTypes(): array
            {
                return $this->discountableItemTypes;
            }
        };
        $applyNominalPromoCodeMiddleware->discountableItemTypes = $discountableItemTypes;

        return $applyNominalPromoCodeMiddleware;
    }

    private function createCurrencyStub(): Currency
    {
        $currency = $this->createStub(Currency::class);
        $currency->method('getCode')->willReturn('CZK');
        $currency->method('getRoundingType')->willReturn(Currency::DEFAULT_ROUNDING_TYPE);
        $currency->method('getRoundingPlacesPriceWithoutVat')->willReturn(Currency::DEFAULT_ROUNDING_PLACES_PRICE_WITHOUT_VAT);

        return $currency;
    }

    private function createCurrencyFacadeStub(): CurrencyFacade
    {
        $currencyFacadeStub = $this->createStub(CurrencyFacade::class);

        $currencyFacadeStub->method('getDomainDefaultCurrencyByDomainId')->willReturn(
            $this->createCurrencyStub(),
        );

        return $currencyFacadeStub;
    }
}
