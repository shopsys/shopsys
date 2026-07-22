<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use DateTimeImmutable;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Order\Item\QuantifiedProduct;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\PersonalPickupPointMiddleware;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\SetExpectedDeliveryDateMiddleware;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\DeliveryDate\TransportExpectedDeliveryDateCalculation;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

final class SetExpectedDeliveryDateMiddlewareTest extends MiddlewareTestCase
{
    public function testExpectedDeliveryDateIsCalculatedForTheTransportProductsAndPickupPlaceOfTheOrderInput(): void
    {
        $expectedDeliveryDate = new DateTimeImmutable('2026-09-10 00:00:00');
        $transportStub = $this->createStub(Transport::class);
        $quantifiedProduct = new QuantifiedProduct($this->createStub(Product::class), 2);

        $orderProcessingData = $this->createOrderProcessingData();
        $orderProcessingData->orderInput->setTransport($transportStub);
        $orderProcessingData->orderInput->addQuantifiedProduct($quantifiedProduct);
        $orderProcessingData->orderInput->addAdditionalData(
            PersonalPickupPointMiddleware::ADDITIONAL_DATA_PICKUP_PLACE_IDENTIFIER,
            'selected-store-uuid',
        );

        $transportExpectedDeliveryDateCalculationMock = $this->createMock(TransportExpectedDeliveryDateCalculation::class);
        $transportExpectedDeliveryDateCalculationMock
            ->expects($this->once())
            ->method('calculateExpectedDeliveryDateForQuantifiedProducts')
            ->with($transportStub, [$quantifiedProduct], Domain::FIRST_DOMAIN_ID, 'selected-store-uuid')
            ->willReturn($expectedDeliveryDate);

        $result = (new SetExpectedDeliveryDateMiddleware($transportExpectedDeliveryDateCalculationMock))
            ->handle($orderProcessingData, $this->createOrderProcessingStack());

        $this->assertSame($expectedDeliveryDate, $result->orderData->expectedDeliveryDate);
    }

    public function testExpectedDeliveryDateStaysUnsetWithoutTransport(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $transportExpectedDeliveryDateCalculationStub = $this->createStub(TransportExpectedDeliveryDateCalculation::class);
        $transportExpectedDeliveryDateCalculationStub
            ->method('calculateExpectedDeliveryDateForQuantifiedProducts')
            ->willReturn(new DateTimeImmutable('2026-09-10 00:00:00'));

        $result = (new SetExpectedDeliveryDateMiddleware($transportExpectedDeliveryDateCalculationStub))
            ->handle($orderProcessingData, $this->createOrderProcessingStack());

        $this->assertNull($result->orderData->expectedDeliveryDate);
    }
}
