<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Transport;

use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Config\DomainConfig;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Cart\Cart;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInput;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderInputFactory;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessingFacade;
use Shopsys\FrameworkBundle\Model\Order\Processing\OrderProcessorMiddleware\AddTransportMiddleware;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceCalculation;
use Shopsys\FrameworkBundle\Model\Transport\TransportPriceProvider;

class TransportPriceProviderTest extends TestCase
{
    private const int CART_TOTAL_WEIGHT = 2500;

    public function testTransportPriceIsCalculatedFromTheProcessedCartWithoutTheTransportBeingSet(): void
    {
        $domainConfig = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com', 'example', 'en', new DateTimeZone('UTC'), 'http://example.com');
        $transport = $this->createStub(Transport::class);
        $transportPrice = new Price(Money::create(100), Money::create(121));

        $orderInput = new OrderInput($domainConfig);
        $orderInput->addAdditionalData(AddTransportMiddleware::ADDITIONAL_DATA_CART_TOTAL_WEIGHT, self::CART_TOTAL_WEIGHT);
        $orderInputFactoryStub = $this->createStub(OrderInputFactory::class);
        $orderInputFactoryStub->method('createFromCart')->willReturn($orderInput);

        $orderData = $this->createStub(OrderData::class);
        $orderProcessingFacadeMock = $this->createMock(OrderProcessingFacade::class);
        $orderProcessingFacadeMock
            ->expects($this->once())
            ->method('getProcessedOrderData')
            ->with($this->callback(static fn (OrderInput $processedOrderInput): bool => $processedOrderInput->getTransport() === null))
            ->willReturn($orderData);

        $transportPriceCalculationMock = $this->createMock(TransportPriceCalculation::class);
        $transportPriceCalculationMock
            ->expects($this->once())
            ->method('calculatePriceForProcessedOrder')
            ->with($transport, $orderData, Domain::FIRST_DOMAIN_ID, self::CART_TOTAL_WEIGHT)
            ->willReturn($transportPrice);

        $transportPriceProvider = new TransportPriceProvider($orderInputFactoryStub, $orderProcessingFacadeMock, $transportPriceCalculationMock);

        $this->assertSame($transportPrice, $transportPriceProvider->getTransportPrice($this->createStub(Cart::class), $transport, $domainConfig));
    }
}
