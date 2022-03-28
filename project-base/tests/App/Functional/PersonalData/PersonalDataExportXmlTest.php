<?php

declare(strict_types=1);

namespace Tests\App\Functional\PersonalData;

use App\Model\Customer\User\CustomerUser;
use App\Model\Customer\User\CustomerUserData;
use App\Model\Order\Item\OrderItem;
use App\Model\Order\Order;
use App\Model\Order\OrderData;
use App\Model\Product\Product;
use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Component\Xml\XmlNormalizer;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyData;
use Shopsys\FrameworkBundle\Model\Pricing\Price;
use Tests\App\Test\TransactionFunctionalTestCase;
use Twig\Environment;

class PersonalDataExportXmlTest extends TransactionFunctionalTestCase
{
    protected const EMAIL = 'no-reply@shopsys.com';
    protected const EXPECTED_XML_FILE_NAME = 'test.xml';
    protected const DOMAIN_ID_FIRST = Domain::FIRST_DOMAIN_ID;

    /**
     * @var \Twig\Environment
     * @inject
     */
    private Environment $twigEnvironment;

    public function testExportXml()
    {
        $country = $this->createCountry();

        $customerData = new CustomerData();
        $customerData->domainId = Domain::FIRST_DOMAIN_ID;
        $customer = new Customer($customerData);

        $customerData->billingAddress = $this->createBillingAddress($country, $customer);
        $deliveryAddress = $this->createDeliveryAddress($country);
        $customerData->deliveryAddresses[] = $deliveryAddress;

        $customer->edit($customerData);

        $customerUser = $this->createCustomerUser($customer);
        $status = $this->createMock(OrderStatus::class);
        $currencyData = new CurrencyData();
        $currencyData->name = 'CZK';
        $currencyData->code = 'CZK';
        $currency = new Currency($currencyData);
        $order = $this->createOrder($currency, $status, $country);
        /** @var \App\Model\Product\Product $product */
        $product = $this->createMock(Product::class);
        $price = new Price(Money::create(1), Money::create(1));
        $orderItem = new OrderItem($order, 'test', $price, '1', 1, OrderItem::TYPE_PRODUCT, 'ks', 'cat');
        $orderItem->setProduct($product);
        $order->addItem($orderItem);
        $order->setStatus($status);

        $generatedXml = $this->twigEnvironment->render('Front/Content/PersonalData/export.xml.twig', [
            'customerUser' => $customerUser,
            'orders' => [0 => $order],
            'newsletterSubscriber' => null,
        ]);

        $generatedXml = XmlNormalizer::normalizeXml($generatedXml);

        $expectedXml = file_get_contents(__DIR__ . '/Resources/' . self::EXPECTED_XML_FILE_NAME);
        $this->assertEquals($expectedXml, $generatedXml);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country
     */
    private function createCountry()
    {
        $countryData = new CountryData();
        $countryData->names = ['cz' => 'Czech Republic'];
        $countryData->code = 'CZ';
        return new Country($countryData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    private function createBillingAddress(Country $country, Customer $customer)
    {
        $billingAddressData = new BillingAddressData();
        $billingAddressData->country = $country;
        $billingAddressData->city = 'Ostrava';
        $billingAddressData->street = 'Hlubinská';
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'Shopsys';
        $billingAddressData->companyNumber = '123456';
        $billingAddressData->companyTaxNumber = '123456';
        $billingAddressData->postcode = '70200';
        $billingAddressData->customer = $customer;

        return new BillingAddress($billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress
     */
    private function createDeliveryAddress(Country $country)
    {
        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->country = $country;
        $deliveryAddressData->telephone = '+420987654321';
        $deliveryAddressData->postcode = '70200';
        $deliveryAddressData->companyName = 'Shopsys';
        $deliveryAddressData->street = 'Hlubinská';
        $deliveryAddressData->city = 'Ostrava';
        $deliveryAddressData->lastName = 'Fero';
        $deliveryAddressData->firstName = 'Mrkva';
        return new DeliveryAddress($deliveryAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\User\CustomerUser
     */
    private function createCustomerUser(Customer $customer)
    {
        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'Jaromír';
        $customerUserData->lastName = 'Jágr';
        $customerUserData->domainId = self::DOMAIN_ID_FIRST;
        $customerUserData->createdAt = new DateTime('2018-04-13');
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->telephone = '+420987654321';
        $customerUserData->customer = $customer;

        return new CustomerUser($customerUserData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\Currency $currency
     * @param \Shopsys\FrameworkBundle\Model\Order\Status\OrderStatus $status
     * @param \Shopsys\FrameworkBundle\Model\Country\Country $country
     * @return \App\Model\Order\Order
     */
    private function createOrder(Currency $currency, OrderStatus $status, Country $country)
    {
        $orderData = new OrderData();
        $orderData->currency = $currency;
        $orderData->status = $status;
        $orderData->email = 'no-reply@shopsys.com';
        $orderData->createdAt = new DateTime('2018-04-13');
        $orderData->domainId = self::DOMAIN_ID_FIRST;
        $orderData->lastName = 'Bořič';
        $orderData->firstName = 'Adam';
        $orderData->city = 'Liberec';
        $orderData->street = 'Cihelní 5';
        $orderData->companyName = 'Shopsys';
        $orderData->postcode = '65421';
        $orderData->telephone = '+420987654321';
        $orderData->companyTaxNumber = '123456';
        $orderData->companyNumber = '123456';
        $orderData->deliveryAddressSameAsBillingAddress = true;
        $orderData->country = $country;

        return new Order($orderData, '1523596513', 'hash');
    }
}
