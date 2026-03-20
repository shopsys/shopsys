<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemPriceCalculation;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderPriceCalculation;
use Shopsys\FrameworkBundle\Model\Payment\OrderRoundingTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Pricing\Rounding;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat;
use Tests\FrameworkBundle\Test\IsMoneyEqual;

class OrderPriceCalculationTest extends TestCase
{
    public function testGetOrderTotalPrice(): void
    {
        $orderItems = [
            $this->createOrderProductStub(),
            $this->createOrderProductStub(),
            $this->createOrderPaymentStub(),
            $this->createOrderTransportStub(),
        ];

        $pricesMap = [
            [$orderItems[0], new Price(Money::create(150), Money::create(200))],
            [$orderItems[1], new Price(Money::create(1000), Money::create(3000))],
            [$orderItems[2], new Price(Money::create(15), Money::create(20))],
            [$orderItems[3], new Price(Money::create(0), Money::create(0))],
        ];

        $roundingStub = $this->createStub(Rounding::class);

        $orderItemPriceCalculationMock = $this->getMockBuilder(OrderItemPriceCalculation::class)
            ->onlyMethods(['__construct', 'calculateTotalPrice'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderItemPriceCalculationMock
            ->expects($this->exactly(4))
            ->method('calculateTotalPrice')
            ->willReturnMap($pricesMap);

        $priceCalculation = new OrderPriceCalculation($orderItemPriceCalculationMock, $roundingStub, new OrderRoundingTypeEnum());

        $orderMock = $this->getMockBuilder(Order::class)
            ->onlyMethods(['__construct', 'getItems'])
            ->disableOriginalConstructor()
            ->getMock();
        $orderMock->expects($this->once())->method('getItems')->willReturn($orderItems);

        $orderTotalPrice = $priceCalculation->getOrderTotalPrice($orderMock);

        $this->assertThat($orderTotalPrice->getPrice()->getPriceWithVat(), new IsMoneyEqual(Money::create(3220)));
        $this->assertThat($orderTotalPrice->getPrice()->getPriceWithoutVat(), new IsMoneyEqual(Money::create(1165)));
        $this->assertThat($orderTotalPrice->getProductPrice()->getPriceWithVat(), new IsMoneyEqual(Money::create(3200)));
    }

    public function testCalculateOrderRoundingPriceForNoneType(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::NONE);
        $orderTotalPrice = new Price(Money::create(100), Money::create(120));

        $priceCalculation = $this->createOrderPriceCalculation();

        $this->assertNull($priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_INTEGER, $orderTotalPrice, Domain::FIRST_DOMAIN_ID));
    }

    public function testCalculateOrderRoundingPriceWholeDown(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::WHOLE);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.3'));

        $priceCalculation = $this->createOrderPriceCalculation();
        $roundingPrice = $priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_INTEGER, $orderTotalPrice, Domain::FIRST_DOMAIN_ID)->getPriceWithVat();

        $this->assertThat($roundingPrice, new IsMoneyEqual(Money::create('-0.3')));
    }

    public function testCalculateOrderRoundingPriceWholeUp(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::WHOLE);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.9'));

        $priceCalculation = $this->createOrderPriceCalculation();
        $roundingPrice = $priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_INTEGER, $orderTotalPrice, Domain::FIRST_DOMAIN_ID)->getPriceWithVat();

        $this->assertThat($roundingPrice, new IsMoneyEqual(Money::create('0.1')));
    }

    public function testCalculateOrderRoundingPriceForEurFiveCentsDown(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::FIVE_CENTS);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.12'));

        $priceCalculation = $this->createOrderPriceCalculation();
        $roundingPrice = $priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_HUNDREDTHS, $orderTotalPrice, Domain::FIRST_DOMAIN_ID)->getPriceWithVat();

        $this->assertThat($roundingPrice, new IsMoneyEqual(Money::create('-0.02')));
    }

    public function testCalculateOrderRoundingPriceForEurFiveCentsUp(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::FIVE_CENTS);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.13'));

        $priceCalculation = $this->createOrderPriceCalculation();
        $roundingPrice = $priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_HUNDREDTHS, $orderTotalPrice, Domain::FIRST_DOMAIN_ID)->getPriceWithVat();

        $this->assertThat($roundingPrice, new IsMoneyEqual(Money::create('0.02')));
    }

    public function testCalculateOrderRoundingPriceForEurAlreadyRounded(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::FIVE_CENTS);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.15'));

        $priceCalculation = $this->createOrderPriceCalculation();

        $this->assertNull($priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_HUNDREDTHS, $orderTotalPrice, Domain::FIRST_DOMAIN_ID));
    }

    public function testFiveCentsRoundingOnIntegerRoundedCurrencyProducesNoRounding(): void
    {
        $payment = $this->createPayment(OrderRoundingTypeEnum::FIVE_CENTS);
        $orderTotalPrice = new Price(Money::create(100), Money::create('120.12'));

        $orderItemPriceCalculationStub = $this->createStub(OrderItemPriceCalculation::class);
        $priceCalculation = new OrderPriceCalculation($orderItemPriceCalculationStub, new Rounding(), new OrderRoundingTypeEnum());

        $this->assertNull($priceCalculation->calculateOrderRoundingPrice($payment, Currency::ROUNDING_TYPE_INTEGER, $orderTotalPrice, Domain::FIRST_DOMAIN_ID));
    }

    private function createPayment(string $orderRoundingType): Payment
    {
        $paymentData = new PaymentData();
        $paymentData->enabled = [Domain::FIRST_DOMAIN_ID => true];
        $paymentData->vatsIndexedByDomainId = [Domain::FIRST_DOMAIN_ID => $this->createStub(Vat::class)];
        $paymentData->orderRoundingTypeByDomainId = [Domain::FIRST_DOMAIN_ID => $orderRoundingType];

        return new Payment($paymentData);
    }

    private function createOrderPriceCalculation(): OrderPriceCalculation
    {
        $roundingStub = $this->createStub(Rounding::class);
        $roundingStub->method('roundPriceWithVat')->willReturnCallback(fn (Money $value) => $value->round(2));
        $orderItemPriceCalculationStub = $this->createStub(OrderItemPriceCalculation::class);

        return new OrderPriceCalculation($orderItemPriceCalculationStub, $roundingStub, new OrderRoundingTypeEnum());
    }

    protected function createOrderProductStub(): OrderItem
    {
        $orderProductStub = $this->createStub(OrderItem::class);

        $orderProductStub->method('isTypeProduct')->willReturn(true);

        return $orderProductStub;
    }

    protected function createOrderPaymentStub(): OrderItem
    {
        $orderProductStub = $this->createStub(OrderItem::class);

        $orderProductStub->method('isTypePayment')->willReturn(true);

        return $orderProductStub;
    }

    protected function createOrderTransportStub(): OrderItem
    {
        $orderProductStub = $this->createStub(OrderItem::class);

        $orderProductStub->method('isTypeTransport')->willReturn(true);

        return $orderProductStub;
    }
}
