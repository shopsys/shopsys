<?php

namespace Tests\FrameworkBundle\Unit\Model\Order;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Order\OrderData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatusData;
use Shopsys\FrameworkBundle\Model\Payment\Payment;
use Shopsys\FrameworkBundle\Model\Payment\PaymentData;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData;

class TestOrderProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Order\OrderData
     */
    public static function getTestOrderData(): OrderData
    {
        $orderData = new OrderData();
        $countryData = new CountryData();
        $countryData->names = ['cs' => 'Slovenská republika'];
        $country = new Country($countryData);

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

        $orderStatusData = new OrderStatusData();
        $orderStatusData->name = ['en' => 'orderStatusName'];
        $orderData->status = new OrderStatus($orderStatusData, OrderStatus::TYPE_NEW);

        $transportData = new TransportData();
        $transportData->name = ['cs' => 'transportName'];
        $orderData->transport = new Transport($transportData);

        $paymentData = new PaymentData();
        $paymentData->name = ['cs' => 'paymentName'];
        $orderData->payment = new Payment($paymentData);

        $currencyData = new CurrencyData();
        $currencyData->name = 'currencyName';
        $currencyData->code = Currency::CODE_CZK;
        $currencyData->exchangeRate = '1.0';
        $currencyData->minFractionDigits = 2;
        $currencyData->roundingType = Currency::ROUNDING_TYPE_INTEGER;
        $orderData->currency = new Currency($currencyData);

        return $orderData;
    }
}
