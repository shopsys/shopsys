<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\FreeTransportAndPaymentInformationMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class FreeTransportAndPaymentInformationMiddlewareTest extends MiddlewareTestCase
{
    public static function freeTransportAndPaymentInformationProvider(): iterable
    {
        yield [true];

        yield [false];
    }

    #[DataProvider('freeTransportAndPaymentInformationProvider')]
    public function testFreeTransportAndPaymentInformationIsProperlySet(bool $expectedValue): void
    {
        $freeTransportAndPaymentFacadeStub = $this->createStub(FreeTransportAndPaymentFacade::class);
        $freeTransportAndPaymentFacadeStub
            ->method('isFreeTransportAndPaymentApplied')
            ->willReturn($expectedValue);

        $freeTransportAndPaymentInformationMiddleware = new FreeTransportAndPaymentInformationMiddleware($freeTransportAndPaymentFacadeStub);

        $orderProcessingData = $this->createOrderProcessingData();

        $result = $freeTransportAndPaymentInformationMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        self::assertSame($expectedValue, $result->orderData->freeTransportAndPaymentApplied);
    }

    public function testDecisionIsBasedOnProductsPriceIncludingAdditionalServices(): void
    {
        $freeTransportAndPaymentFacadeMock = $this->createMock(FreeTransportAndPaymentFacade::class);
        $freeTransportAndPaymentFacadeMock
            ->expects($this->once())
            ->method('isFreeTransportAndPaymentApplied')
            ->with(
                $this->anything(),
                new IsPriceEqual(new Price(Money::create(1100), Money::create(1331))),
                $this->anything(),
            )
            ->willReturn(true);

        $freeTransportAndPaymentInformationMiddleware = new FreeTransportAndPaymentInformationMiddleware($freeTransportAndPaymentFacadeMock);

        $orderProcessingData = $this->createOrderProcessingData();
        $orderProcessingData->orderData->addTotalPrice(new Price(Money::create(1000), Money::create(1210)), OrderItemTypeEnum::TYPE_PRODUCT);
        $orderProcessingData->orderData->addTotalPrice(new Price(Money::create(100), Money::create(121)), OrderItemTypeEnum::TYPE_ADDITIONAL_SERVICE);

        $result = $freeTransportAndPaymentInformationMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());

        self::assertTrue($result->orderData->freeTransportAndPaymentApplied);
    }
}
