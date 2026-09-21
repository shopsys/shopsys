<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Payment;

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
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\PaymentPriceProvider;
use Shopsys\FrameworkBundle\Model\Pricing\Price;

class PaymentPriceProviderTest extends TestCase
{
    public function testPaymentPriceIsCalculatedFromTheProcessedCartWithoutThePaymentBeingSet(): void
    {
        $domainConfig = new DomainConfig(Domain::FIRST_DOMAIN_ID, 'http://example.com', 'example', 'en', new DateTimeZone('UTC'), 'http://example.com');
        $payment = $this->createStub(Payment::class);
        $paymentPrice = new Price(Money::create(50), Money::create('60.5'));

        $orderInputFactoryStub = $this->createStub(OrderInputFactory::class);
        $orderInputFactoryStub->method('createFromCart')->willReturn(new OrderInput($domainConfig));

        $orderData = $this->createStub(OrderData::class);
        $orderProcessingFacadeMock = $this->createMock(OrderProcessingFacade::class);
        $orderProcessingFacadeMock
            ->expects($this->once())
            ->method('getProcessedOrderData')
            ->with($this->callback(static fn (OrderInput $processedOrderInput): bool => $processedOrderInput->getPayment() === null))
            ->willReturn($orderData);

        $paymentPriceCalculationMock = $this->createMock(PaymentPriceCalculation::class);
        $paymentPriceCalculationMock
            ->expects($this->once())
            ->method('calculatePriceForProcessedOrder')
            ->with($payment, $orderData, Domain::FIRST_DOMAIN_ID)
            ->willReturn($paymentPrice);

        $paymentPriceProvider = new PaymentPriceProvider($orderInputFactoryStub, $orderProcessingFacadeMock, $paymentPriceCalculationMock);

        $this->assertSame($paymentPrice, $paymentPriceProvider->getPaymentPrice($this->createStub(Cart::class), $payment, $domainConfig));
    }
}
