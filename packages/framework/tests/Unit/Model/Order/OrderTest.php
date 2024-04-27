<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Order;

use DateTime;
use DateTimeInterface;
use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\FrameworkBundle\Test\Provider\TestOrderProvider;

class OrderTest extends TestCase
{
    public function testGetProductItems()
    {
        $payment = new Payment(new PaymentData());
        $orderData = TestOrderProvider::getTestOrderData();
        $paymentPrice = Price::zero();

        $order = new Order($orderData, 'orderNumber', 'urlHash', null);
        $orderProduct = new OrderItem(
            $order,
            'productName',
            $paymentPrice,
            '0',
            1,
            OrderItemTypeEnum::TYPE_PRODUCT,
            null,
            null,
        );
        $orderPayment = new OrderItem(
            $order,
            'paymentName',
            $paymentPrice,
            '0',
            1,
            OrderItemTypeEnum::TYPE_PAYMENT,
            null,
            null,
        );
        $orderPayment->setPayment($payment);
        $order->addItem($orderProduct);
        $order->addItem($orderPayment);

        $productItems = $order->getProductItems();

        $this->assertCount(1, $productItems);
        $this->assertContainsOnlyInstancesOf(OrderItem::class, $productItems);
    }

    public function testGetProductItemsCount(): void
    {
        $payment = new Payment(new PaymentData());
        $paymentItemPrice = Price::zero();
        $orderData = TestOrderProvider::getTestOrderData();

        $order = new Order($orderData, 'orderNumber', 'urlHash', null);
        $productItem = new OrderItem(
            $order,
            'productName',
            $paymentItemPrice,
            '0',
            1,
            OrderItemTypeEnum::TYPE_PRODUCT,
            null,
            null,
        );
        $paymentItem = new OrderItem(
            $order,
            'paymentName',
            $paymentItemPrice,
            '0',
            1,
            OrderItemTypeEnum::TYPE_PAYMENT,
            null,
            null,
        );
        $paymentItem->setPayment($payment);
        $order->addItem($productItem);
        $order->addItem($paymentItem);

        $this->assertCount(1, $order->getProductItems());
    }

    public function testOrderWithDeliveryAddressSameAsBillingAddress()
    {
        $orderData = TestOrderProvider::getTestOrderData();
        $countryData = new CountryData();
        $countryData->names = ['cs' => 'Slovenská republika'];
        $country = new Country($countryData);

        $orderData->companyName = 'companyName';
        $orderData->telephone = 'telephone';
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $country;
        $orderData->deliveryAddressSameAsBillingAddress = true;

        $order = new Order($orderData, 'orderNumber', 'urlHash', null);

        $this->assertSame('companyName', $order->getDeliveryCompanyName());
        $this->assertSame('telephone', $order->getDeliveryTelephone());
        $this->assertSame('firstName', $order->getDeliveryFirstName());
        $this->assertSame('lastName', $order->getDeliveryLastName());
        $this->assertSame('street', $order->getDeliveryStreet());
        $this->assertSame('city', $order->getDeliveryCity());
        $this->assertSame('postcode', $order->getDeliveryPostcode());
        $this->assertSame($country, $order->getDeliveryCountry());
    }

    public function testOrderWithoutDeliveryAddressSameAsBillingAddress()
    {
        $orderData = TestOrderProvider::getTestOrderData();
        $order = new Order($orderData, 'orderNumber', 'urlHash', null);

        $this->assertSame('deliveryCompanyName', $order->getDeliveryCompanyName());
        $this->assertSame('deliveryTelephone', $order->getDeliveryTelephone());
        $this->assertSame('deliveryFirstName', $order->getDeliveryFirstName());
        $this->assertSame('deliveryLastName', $order->getDeliveryLastName());
        $this->assertSame('deliveryStreet', $order->getDeliveryStreet());
        $this->assertSame('deliveryCity', $order->getDeliveryCity());
        $this->assertSame('deliveryPostcode', $order->getDeliveryPostCode());
        $this->assertSame($orderData->country, $order->getDeliveryCountry());
    }

    public function testOrderCreatedWithEmptyCreatedAtIsCreatedNow()
    {
        $orderData = TestOrderProvider::getTestOrderData();
        $customerUser = null;

        $orderData->createdAt = null;
        $order = new Order($orderData, 'orderNumber', 'urlHash', $customerUser);

        $this->assertDateTimeIsCloseTo(new DateTime(), $order->getCreatedAt(), 5);
    }

    public function testOrderCanBeCreatedWithSpecificCreatedAt()
    {
        $orderData = TestOrderProvider::getTestOrderData();
        $customerUser = null;

        $createAt = new DateTime('2000-01-01 01:00:00');
        $orderData->createdAt = $createAt;
        $order = new Order($orderData, 'orderNumber', 'urlHash', $customerUser);

        $this->assertEquals($createAt, $order->getCreatedAt());
    }

    /**
     * @param \DateTimeInterface $expected
     * @param \DateTimeInterface $actual
     * @param int $deltaInSeconds
     */
    private function assertDateTimeIsCloseTo(DateTimeInterface $expected, DateTimeInterface $actual, $deltaInSeconds)
    {
        $diffInSeconds = $expected->getTimestamp() - $actual->getTimestamp();

        if (abs($diffInSeconds) <= $deltaInSeconds) {
            return;
        }

        $message = sprintf(
            'Failed asserting that %s is close to %s (delta: %d seconds)',
            $expected->format(DateTime::ISO8601),
            $actual->format(DateTime::ISO8601),
            $deltaInSeconds,
        );
        $this->fail($message);
    }
}
