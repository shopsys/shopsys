<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order\Item;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\MainVariantCannotBeOrderedException;
use Shopsys\FrameworkBundle\Model\Order\Item\Exception\WrongItemTypeException;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Product\Product;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Tests\FrameworkBundle\Test\IsMoneyEqual;
use Tests\FrameworkBundle\Unit\Model\Product\TestProductProvider;

class OrderItemTest extends TestCase
{
    public function testTransportCannotBeSetForWrongType(): void
    {
        $orderItem = $this->createOrderPayment();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->setTransport($this->createTransportStub());
    }

    public function testTransportCannotBeGottenFromWrongType(): void
    {
        $orderItem = $this->createOrderPayment();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->getTransport();
    }

    public function testEditTransportTypeEditsTransport(): void
    {
        $orderItem = $this->createOrderTransport();

        $orderItemData = new OrderItemData();
        $orderItemData->name = 'order item transport';
        $orderItemData->unitPriceWithVat = Money::zero();
        $orderItemData->unitPriceWithoutVat = Money::zero();
        $orderItemData->vatPercent = '0';
        $orderItemData->quantity = 1;
        $transport = $this->createTransportStub();
        $orderItemData->transport = $transport;
        $orderItem->edit($orderItemData);

        $this->assertSame($transport, $orderItem->getTransport());
    }

    public function testPaymentCannotBeSetForWrongType(): void
    {
        $orderItem = $this->createOrderProduct();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->setPayment($this->createPaymentStub());
    }

    public function testPaymentCannotBeGottenFromWrongType(): void
    {
        $orderItem = $this->createOrderProduct();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->getPayment();
    }

    public function testEditPaymentTypeEditsPayment(): void
    {
        $orderItem = $this->createOrderPayment();

        $orderItemData = new OrderItemData();
        $orderItemData->name = 'order item payment';
        $orderItemData->unitPriceWithVat = Money::zero();
        $orderItemData->unitPriceWithoutVat = Money::zero();
        $orderItemData->vatPercent = '0';
        $orderItemData->quantity = 1;
        $payment = $this->createPaymentStub();
        $orderItemData->payment = $payment;
        $orderItem->edit($orderItemData);

        $this->assertSame($payment, $orderItem->getPayment());
    }

    public function testProductCannotBeSetForWrongType(): void
    {
        $orderItem = $this->createOrderTransport();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->setProduct($this->createProductStub());
    }

    public function testProductCannotBeGottenFromWrongType(): void
    {
        $orderItem = $this->createOrderTransport();

        $this->expectException(WrongItemTypeException::class);
        $orderItem->getProduct();
    }

    public function testTransportDoesNotHaveProduct(): void
    {
        $orderItem = $this->createOrderTransport();

        $this->assertFalse($orderItem->isTypeProductAndHasProduct());
    }

    public function testEditProductTypeWithProduct(): void
    {
        $orderItemData = new OrderItemData();
        $orderItemData->name = 'newName';
        $orderItemData->unitPriceWithVat = Money::create(20);
        $orderItemData->unitPriceWithoutVat = Money::create(30);
        $orderItemData->quantity = 2;
        $orderItemData->vatPercent = '10';

        $orderItem = $this->createOrderProduct($this->createProductStub());
        $orderItem->edit($orderItemData);

        $this->assertSame('newName', $orderItem->getName());
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(20)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(30)));
        $this->assertSame(2, $orderItem->getQuantity());
        $this->assertSame('10.000000', $orderItem->getvatPercent());
    }

    public function testEditProductTypeWithoutProduct(): void
    {
        $orderItemData = new OrderItemData();
        $orderItemData->name = 'newName';
        $orderItemData->unitPriceWithVat = Money::create(20);
        $orderItemData->unitPriceWithoutVat = Money::create(30);
        $orderItemData->quantity = 2;
        $orderItemData->vatPercent = '10';

        $orderItem = $this->createOrderProduct();
        $orderItem->edit($orderItemData);

        $this->assertSame('newName', $orderItem->getName());
        $this->assertThat($orderItem->getUnitPriceWithVat(), new IsMoneyEqual(Money::create(20)));
        $this->assertThat($orderItem->getUnitPriceWithoutVat(), new IsMoneyEqual(Money::create(30)));
        $this->assertSame(2, $orderItem->getQuantity());
        $this->assertSame('10.000000', $orderItem->getvatPercent());
    }

    public function testConstructWithMainVariantThrowsException(): void
    {
        $variant = Product::create(TestProductProvider::getTestProductData());
        $mainVariant = Product::createMainVariant(TestProductProvider::getTestProductData(), [$variant]);

        $this->expectException(MainVariantCannotBeOrderedException::class);

        $this->createOrderProduct($mainVariant);
    }

    public function testVatPercentIsNormalizedToSixDecimalPlacesOnConstruct(): void
    {
        $orderItem = $this->createOrderProduct();

        $this->assertSame('0.200000', $orderItem->getVatPercent());
    }

    public function testProductGiftItemAcceptsProduct(): void
    {
        $orderItem = new OrderItem(
            $this->createOrderStub(),
            '',
            new Price(Money::create(10), Money::create(12)),
            '0.2',
            1,
            OrderItemTypeEnum::TYPE_PRODUCT_GIFT,
            null,
            null,
        );
        $product = $this->createProductStub();

        $orderItem->setProductGift($product);

        $this->assertSame($product, $orderItem->getProductGift());
    }

    private function createOrderPayment(): OrderItem
    {
        $orderPayment = new OrderItem(
            $this->createOrderStub(),
            '',
            new Price(Money::create(10), Money::create(12)),
            '0.2',
            1,
            OrderItemTypeEnum::TYPE_PAYMENT,
            null,
            null,
        );

        $paymentStub = $this->createPaymentStub();
        $orderPayment->setPayment($paymentStub);

        return $orderPayment;
    }

    private function createOrderTransport(): OrderItem
    {
        $orderTransport = new OrderItem(
            $this->createOrderStub(),
            '',
            new Price(Money::create(10), Money::create(12)),
            '0.2',
            1,
            OrderItemTypeEnum::TYPE_TRANSPORT,
            null,
            null,
        );
        $orderTransport->setTransport($this->createTransportStub());

        return $orderTransport;
    }

    private function createOrderProduct(?Product $product = null): OrderItem
    {
        $orderProduct = new OrderItem(
            $this->createOrderStub(),
            '',
            new Price(Money::create(10), Money::create(12)),
            '0.2',
            1,
            OrderItemTypeEnum::TYPE_PRODUCT,
            null,
            null,
        );
        $orderProduct->setProduct($product);

        return $orderProduct;
    }

    private function createOrderStub(): Order
    {
        return $this->createStub(Order::class);
    }

    private function createTransportStub(): Transport
    {
        return $this->createStub(Transport::class);
    }

    private function createPaymentStub(): Payment
    {
        return $this->createStub(Payment::class);
    }

    private function createProductStub(): Product
    {
        return $this->createStub(Product::class);
    }
}
