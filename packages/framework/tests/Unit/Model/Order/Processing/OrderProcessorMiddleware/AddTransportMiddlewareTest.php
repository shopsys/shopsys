<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Processing\OrderProcessorMiddleware;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesData;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Tests\FrameworkBundle\Test\IsPriceEqual;
use Tests\FrameworkBundle\Test\MiddlewareTestCase;

class AddTransportMiddlewareTest extends MiddlewareTestCase
{
    public function testTransportIsAdded(): void
    {
        $expectedTransportName = 'myTransportName';
        $expectedPrice = new Price(Money::create('100'), Money::create('121'));

        $orderProcessingData = $this->createOrderProcessingData();

        $transportData = new TransportData();
        $transportData->name = ['en' => $expectedTransportName];
        $transportData->enabled = [1 => true];
        $transportInputPricesData = new TransportInputPricesData();
        $transportInputPricesData->vat = $this->createVat();
        $transportData->inputPricesByDomain = [1 => $transportInputPricesData];
        $transport = new Transport($transportData);

        $orderProcessingData->orderInput->setTransport($transport);

        $addTransportMiddleware = $this->createAddTransportMiddleware($expectedPrice);

        $result = $addTransportMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertSame($actualOrderData->transport, $transport);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_TRANSPORT]),
            new IsPriceEqual($expectedPrice),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual($expectedPrice),
        );

        $actualTransportItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT);

        $this->assertCount(1, $actualTransportItemsType);
        $this->assertCount(1, $actualOrderData->items);

        $this->assertSame($actualTransportItemsType[0]->transport, $transport);
        $this->assertSame($expectedTransportName, $actualTransportItemsType[0]->name);
    }

    public function testTransportIsIgnoredIfMissing(): void
    {
        $orderProcessingData = $this->createOrderProcessingData();

        $addTransportMiddleware = $this->createAddTransportMiddleware(Price::zero());

        $result = $addTransportMiddleware->handle($orderProcessingData, $this->createOrderProcessingStack());
        $actualOrderData = $result->orderData;

        $this->assertNull($actualOrderData->transport);
        $actualTransportItemsType = $actualOrderData->getItemsByType(OrderItemTypeEnum::TYPE_TRANSPORT);

        $this->assertCount(0, $actualTransportItemsType);
        $this->assertCount(0, $actualOrderData->items);

        $this->assertThat(
            $actualOrderData->getTotalPriceForItemTypes([OrderItemTypeEnum::TYPE_TRANSPORT]),
            new IsPriceEqual(Price::zero()),
        );

        $this->assertThat(
            $actualOrderData->totalPrice,
            new IsPriceEqual(Price::zero()),
        );
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $transportPrice
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation
     */
    private function createTransportPriceCalculationMock(Price $transportPrice): TransportPriceCalculation
    {
        $transportPriceCalculation = $this->createMock(TransportPriceCalculation::class);
        $transportPriceCalculation->method('calculatePrice')->willReturn($transportPrice);

        return $transportPriceCalculation;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Price $transportPrice
     * @return \Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware
     */
    private function createAddTransportMiddleware(Price $transportPrice): AddTransportMiddleware
    {
        return new AddTransportMiddleware(
            $this->createTransportPriceCalculationMock($transportPrice),
            $this->createCurrencyFacade(),
            $this->createOrderItemDataFactory(),
        );
    }
}
