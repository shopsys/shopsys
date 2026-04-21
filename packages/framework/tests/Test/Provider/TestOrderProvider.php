<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Provider;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItem;
use Shopsys\FrameworkBundle\Model\Order\Item\OrderItemTypeEnum;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusTypeEnum;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportTypeEnum;

class TestOrderProvider
{
    public static function getTestOrderData(): OrderData
    {
        $orderData = static::createOrderDataInstance();

        $country = static::getCountry();

        $orderData->companyName = 'companyName';
        $orderData->telephone = new PhoneData(number: 'telephone');
        $orderData->email = 'telephone';
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $country;
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCompanyName = 'deliveryCompanyName';
        $orderData->deliveryTelephone = new PhoneData(number: 'deliveryTelephone');
        $orderData->deliveryFirstName = 'deliveryFirstName';
        $orderData->deliveryLastName = 'deliveryLastName';
        $orderData->deliveryStreet = 'deliveryStreet';
        $orderData->deliveryCity = 'deliveryCity';
        $orderData->deliveryPostcode = 'deliveryPostcode';
        $orderData->deliveryCountry = $country;
        $orderData->domainId = Domain::FIRST_DOMAIN_ID;

        $orderStatusData = static::createOrderStatusDataInstance();
        $orderStatusData->name = ['en' => 'orderStatusName'];
        $orderData->status = static::createOrderStatusInstance($orderStatusData);

        $orderData->fillCurrencyFieldsFromCurrency(TestCurrencyProvider::getTestCurrency());

        return $orderData;
    }

    public static function createOrderTransport(Order $order): OrderItem
    {
        $orderTransport = self::createOrderTransportInstance($order);

        $transportData = static::createTransportDataInstance();
        $transportData->name = ['cs' => 'transportName'];
        $transportData->type = TransportTypeEnum::TYPE_COMMON;
        $orderTransport->setTransport(static::createTransportInstance($transportData));

        return $orderTransport;
    }

    protected static function createOrderDataInstance(): OrderData
    {
        return new OrderData();
    }

    public static function createCountryDataInstance(): CountryData
    {
        return new CountryData();
    }

    public static function createCountryInstance(CountryData $countryData): Country
    {
        return new Country($countryData);
    }

    public static function createOrderStatusDataInstance(): OrderStatusData
    {
        return new OrderStatusData();
    }

    public static function createOrderStatusInstance(OrderStatusData $orderStatusData): OrderStatus
    {
        return new OrderStatus($orderStatusData, OrderStatusTypeEnum::TYPE_NEW);
    }

    public static function createTransportDataInstance(): TransportData
    {
        return new TransportData();
    }

    public static function createTransportInstance(TransportData $transportData): Transport
    {
        return new Transport($transportData);
    }

    public static function createPaymentDataInstance(): PaymentData
    {
        return new PaymentData();
    }

    public static function createPaymentInstance(PaymentData $paymentData): Payment
    {
        return new Payment($paymentData);
    }

    public static function getCountry(): Country
    {
        $countryData = static::createCountryDataInstance();
        $countryData->names = ['cs' => 'Slovenská republika'];

        return static::createCountryInstance($countryData);
    }

    public static function createOrderTransportInstance(Order $order): OrderItem
    {
        return new OrderItem(
            $order,
            '',
            new Price(Money::create(10), Money::create(12)),
            '0.2',
            1,
            OrderItemTypeEnum::TYPE_TRANSPORT,
            null,
            null,
        );
    }
}
