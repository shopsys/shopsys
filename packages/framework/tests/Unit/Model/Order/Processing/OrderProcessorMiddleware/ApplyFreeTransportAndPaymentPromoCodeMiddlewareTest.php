<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\ApplyFreeTransportAndPaymentPromoCodeMiddleware;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\CurrentPromoCodeFacade;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCode;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeData;
use Shopsys\FrameworkBundle\Model\Order\PromoCode\PromoCodeTypeEnum;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class ApplyFreeTransportAndPaymentPromoCodeMiddlewareTest extends MiddlewareTestCase
{
    public function testAddPromoCode(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'freeTransportAndPaymentPromoCode';
        $promoCodeData->discountType = PromoCodeTypeEnum::DISCOUNT_TYPE_FREE_TRANSPORT_PAYMENT;
        $promoCode = new PromoCode($promoCodeData);

        $currentPromoCodeFacade = $this->createStub(CurrentPromoCodeFacade::class);

        $applyFreeTransportAndPaymentPromoCodeMiddleware = new ApplyFreeTransportAndPaymentPromoCodeMiddleware($currentPromoCodeFacade);
        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $result = $applyFreeTransportAndPaymentPromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $this->assertSame($promoCode->getCode(), $result->orderData->promoCode);
        $this->assertTrue($result->orderInput->isFreeTransportAndPaymentPromoCodeApplied());
    }

    #[DataProvider('getUnsupportedPromoCodeTypesDataProvider')]
    public function testUnsupportedPromoCodeTypeIsNotAdded(string $promoCodeType): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $promoCodeData = new PromoCodeData();
        $promoCodeData->code = 'promoCode';
        $promoCodeData->discountType = $promoCodeType;
        $promoCode = new PromoCode($promoCodeData);

        $currentPromoCodeFacade = $this->createStub(CurrentPromoCodeFacade::class);

        $applyFreeTransportAndPaymentPromoCodeMiddleware = new ApplyFreeTransportAndPaymentPromoCodeMiddleware($currentPromoCodeFacade);
        $orderProcessingData->orderInput->addPromoCode($promoCode);

        $result = $applyFreeTransportAndPaymentPromoCodeMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        $this->assertNull($result->orderData->promoCode);
        $this->assertFalse($result->orderInput->isFreeTransportAndPaymentPromoCodeApplied());
    }

    public static function getUnsupportedPromoCodeTypesDataProvider(): iterable
    {
        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_PERCENT];

        yield [PromoCodeTypeEnum::DISCOUNT_TYPE_NOMINAL];
    }
}
