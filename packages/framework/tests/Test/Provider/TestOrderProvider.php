<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Test\Provider;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;
use Tests\App\Functional\Model\Pricing\Currency\TestCurrencyProvider;

class TestOrderProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public static function getTestOrderData(): OrderData
    {
        $orderData = static::createOrderDataInstance();

        $country = static::getCountry();

        $orderData->companyName = 'companyName';
        $orderData->telephone = 'telephone';
        $orderData->email = 'telephone';
        $orderData->firstName = 'firstName';
        $orderData->lastName = 'lastName';
        $orderData->street = 'street';
        $orderData->city = 'city';
        $orderData->postcode = 'postcode';
        $orderData->country = $country;
        $orderData->deliveryAddressSameAsBillingAddress = false;
        $orderData->deliveryCompanyName = 'deliveryCompanyName';
        $orderData->deliveryTelephone = 'deliveryTelephone';
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

        $transportData = static::createTransportDataInstance();
        $transportData->name = ['cs' => 'transportName'];
        $orderData->transport = static::createTransportInstance($transportData);

        $paymentData = static::createPaymentDataInstance();
        $paymentData->name = ['cs' => 'paymentName'];
        $orderData->payment = static::createPaymentInstance($paymentData);

        $orderData->currency = TestCurrencyProvider::getTestCurrency();

        return $orderData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    protected static function createOrderDataInstance(): OrderData
    {
        return new OrderData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\CountryData
     */
    public static function createCountryDataInstance(): CountryData
    {
        return new CountryData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\CountryData $countryData
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public static function createCountryInstance(CountryData $countryData): Country
    {
        return new Country($countryData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData
     */
    public static function createOrderStatusDataInstance(): OrderStatusData
    {
        return new OrderStatusData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData $orderStatusData
     * @return \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus
     */
    public static function createOrderStatusInstance(OrderStatusData $orderStatusData): OrderStatus
    {
        return new OrderStatus($orderStatusData, OrderStatus::TYPE_NEW);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    public static function createTransportDataInstance(): TransportData
    {
        return new TransportData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     * @return \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public static function createTransportInstance(TransportData $transportData): Transport
    {
        return new Transport($transportData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Payment\PaymentData
     */
    public static function createPaymentDataInstance(): PaymentData
    {
        return new PaymentData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Payment\PaymentData $paymentData
     * @return \Shopsys\FrameworkBundle\Model\Payment\Payment
     */
    public static function createPaymentInstance(PaymentData $paymentData): Payment
    {
        return new Payment($paymentData);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    public static function getCountry(): Country
    {
        $countryData = static::createCountryDataInstance();
        $countryData->names = ['cs' => 'Slovenská republika'];

        return static::createCountryInstance($countryData);
    }
}
