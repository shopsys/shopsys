<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

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
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Twig\PriceExtension;
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
        $promoCodeData->discountType = PromoCode::DISCOUNT_TYPE_NOMINAL;
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
        $this->assertSame('Promo code -121€', $actualDiscountItemsType[0]->name);
    }

    /**
     * @param int|null $promoCodeType
     */
    #[DataProvider('invalidPromoCodeTypeDataProvider')]
    public function testNoPromoCodeIsAdded(?int $promoCodeType): void
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
     * @return iterable
     */
    public static function invalidPromoCodeTypeDataProvider(): iterable
    {
        yield [PromoCode::DISCOUNT_TYPE_PERCENT];

        yield [null];
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price|null $discountPrice
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyNominalPromoCodeMiddleware
     */
    private function createApplyNominalPromoCodeMiddleware(?Price $discountPrice): ApplyNominalPromoCodeMiddleware
    {
        $currentPromoCodeFacade = $this->createMock(CurrentPromoCodeFacade::class);

        $promoCodeFacade = $this->createMock(PromoCodeFacade::class);
        $promoCodeFacade->method('getHighestLimitByPromoCodeAndTotalPrice')->willReturn(new PromoCodeLimit('1', '10'));

        $discountCalculation = $this->createMock(DiscountCalculation::class);
        $discountCalculation->method('calculateNominalDiscount')->willReturn($discountPrice);

        $priceExtension = $this->createMock(PriceExtension::class);
        $priceExtension->method('priceFilter')->willReturnCallback(function (Money $money) {
            return $money->getAmount() . '€';
        });

        $vatFacade = $this->createMock(VatFacade::class);

        return new ApplyNominalPromoCodeMiddleware(
            $currentPromoCodeFacade,
            $promoCodeFacade,
            $discountCalculation,
            $this->createOrderItemDataFactory(),
            $priceExtension,
            $vatFacade,
        );
    }
}
