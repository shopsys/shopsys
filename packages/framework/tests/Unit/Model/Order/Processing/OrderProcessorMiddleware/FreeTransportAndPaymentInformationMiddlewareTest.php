<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use PHPUnit\Framework\Attributes\DataProvider;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\FreeTransportAndPaymentInformationMiddleware;
use Shopsys\FrameworkBundle\Model\TransportAndPayment\FreeTransportAndPaymentFacade;
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
}
