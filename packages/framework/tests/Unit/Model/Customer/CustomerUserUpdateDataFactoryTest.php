<?php

declare(strict_types=1);

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\CustomerRepository;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory;
use Shopsys\FrameworkBundle\Model\Customer\User\Role\CustomerUserRoleGroupFacade;
use Shopsys\FrameworkBundle\Model\Newsletter\NewsletterFacade;
use Shopsys\FrameworkBundle\Model\Order\Order;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupSettingFacade;
use Tests\FrameworkBundle\Test\Provider\TestOrderProvider;

class CustomerUserUpdateDataFactoryTest extends TestCase
{
    public function testGetAmendedCustomerUserUpdateDataByOrderWithoutChanges()
    {
        $customerUserUpdateUpdateDataFactory = $this->getCustomerUserUpdateDataFactory();

        $customerUserData = TestCustomerProvider::getTestCustomerUserData();
        $customerUserData->password = null;
        $customerUser = new CustomerUser($customerUserData);

        $customer = $customerUser->getCustomer();

        $deliveryAddresses = $customer->getDeliveryAddresses();
        $deliveryAddress = reset($deliveryAddresses);

        $orderData = TestOrderProvider::getTestOrderData();
        $order = new Order(
            $orderData,
            '123456',
            '7ebafe9fe',
        );
        $order->setCompanyInfo(
            'companyName',
            'companyNumber',
            'companyTaxNumber',
        );
        $order->addItem(TestOrderProvider::createOrderTransport($order));

        $customerUserUpdateData = $customerUserUpdateUpdateDataFactory->createAmendedByOrder(
            $customerUser,
            $order,
            $deliveryAddress,
        );

        $this->assertEquals($customerUserData, $customerUserUpdateData->customerUserData);
        $this->assertEquals(TestCustomerProvider::getBillingAddressData($customer), $customerUserUpdateData->billingAddressData);
        $this->assertEquals(TestCustomerProvider::getDeliveryAddressData($customer), $customerUserUpdateData->deliveryAddressData);
    }

    public function testGetAmendedCustomerUserUpdateDataByOrder()
    {
        $customerUserUpdateDataFactory = $this->getCustomerUserUpdateDataFactory();

        $deliveryCountryData = new CountryData();
        $deliveryCountryData->names = ['cs' => 'Slovenská republika'];

        $deliveryCountry = new Country($deliveryCountryData);

        $customerUserData = TestCustomerProvider::getTestCustomerUserData();
        $customerUserData->password = null;
        $customerUser = new CustomerUser($customerUserData);

        $orderData = TestOrderProvider::getTestOrderData();

        $order = new Order(
            $orderData,
            '123456',
            '7eba123456fe9fe',
        );
        $order->setCompanyInfo(
            'companyName',
            'companyNumber',
            'companyTaxNumber',
        );
        $order->addItem(TestOrderProvider::createOrderTransport($order));

        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->addressFilled = true;
        $deliveryAddressData->street = $order->getDeliveryStreet();
        $deliveryAddressData->city = $order->getDeliveryCity();
        $deliveryAddressData->postcode = $order->getDeliveryPostcode();
        $deliveryAddressData->companyName = $order->getDeliveryCompanyName();
        $deliveryAddressData->firstName = $order->getDeliveryFirstName();
        $deliveryAddressData->lastName = $order->getDeliveryLastName();
        $deliveryAddressData->telephone = $order->getDeliveryTelephone();
        $deliveryAddressData->country = $deliveryCountry;

        $customerUserUpdateData = $customerUserUpdateDataFactory->createAmendedByOrder($customerUser, $order, null);

        $this->assertEquals($customerUserData, $customerUserUpdateData->customerUserData);
        $this->assertEquals($deliveryAddressData, $customerUserUpdateData->deliveryAddressData);
        $this->assertTrue($customerUserUpdateData->billingAddressData->companyCustomer);
        $this->assertSame($order->getCompanyName(), $customerUserUpdateData->billingAddressData->companyName);
        $this->assertSame($order->getCompanyNumber(), $customerUserUpdateData->billingAddressData->companyNumber);
        $this->assertSame(
            $order->getCompanyTaxNumber(),
            $customerUserUpdateData->billingAddressData->companyTaxNumber,
        );
        $this->assertSame($order->getStreet(), $customerUserUpdateData->billingAddressData->street);
        $this->assertSame($order->getCity(), $customerUserUpdateData->billingAddressData->city);
        $this->assertSame($order->getPostcode(), $customerUserUpdateData->billingAddressData->postcode);

        $this->assertSame($order->getCountry(), $customerUserUpdateData->deliveryAddressData->country);
    }

    public function testGetAmendedCustomerUserUpdateDataByOrderForSocialNetworkLogin()
    {
        $customerUserUpdateUpdateDataFactory = $this->getCustomerUserUpdateDataFactory();

        $deliveryCountryData = new CountryData();
        $deliveryCountryData->names = ['cs' => 'Slovenská republika'];

        $customerUserData = TestCustomerProvider::getEmptyTestCustomerUserData();
        $customerUserData->email = 'social-network-user@shopsys.com';
        $customerUser = new CustomerUser($customerUserData);

        $deliveryAddress = $customerUser->getDefaultDeliveryAddress();

        $this->assertNull($customerUser->getFirstName());
        $this->assertNull($customerUser->getLastName());
        $this->assertNull($customerUser->getTelephone());

        $orderData = TestOrderProvider::getTestOrderData();
        $order = new Order(
            $orderData,
            '123456',
            '7ebafe9fe',
        );
        $order->setCompanyInfo(
            'companyName',
            'companyNumber',
            'companyTaxNumber',
        );
        $order->addItem(TestOrderProvider::createOrderTransport($order));

        $customerUserUpdateData = $customerUserUpdateUpdateDataFactory->createAmendedByOrder(
            $customerUser,
            $order,
            $deliveryAddress,
        );

        $this->assertSame($order->getFirstName(), $customerUserUpdateData->customerUserData->firstName);
        $this->assertSame($order->getLastName(), $customerUserUpdateData->customerUserData->lastName);
        $this->assertSame($order->getTelephone(), $customerUserUpdateData->customerUserData->telephone);

        $this->assertSame($order->getCompanyName(), $customerUserUpdateData->billingAddressData->companyName);
        $this->assertSame($order->getCompanyNumber(), $customerUserUpdateData->billingAddressData->companyNumber);
        $this->assertSame($order->getCompanyTaxNumber(), $customerUserUpdateData->billingAddressData->companyTaxNumber);
        $this->assertSame($order->getStreet(), $customerUserUpdateData->billingAddressData->street);
        $this->assertSame($order->getCity(), $customerUserUpdateData->billingAddressData->city);
        $this->assertSame($order->getPostcode(), $customerUserUpdateData->billingAddressData->postcode);
        $this->assertSame($order->getCountry(), $customerUserUpdateData->billingAddressData->country);

        $this->assertSame($order->getDeliveryStreet(), $customerUserUpdateData->deliveryAddressData->street);
        $this->assertSame($order->getDeliveryCity(), $customerUserUpdateData->deliveryAddressData->city);
        $this->assertSame($order->getDeliveryPostcode(), $customerUserUpdateData->deliveryAddressData->postcode);
        $this->assertSame($order->getDeliveryCountry(), $customerUserUpdateData->deliveryAddressData->country);
        $this->assertSame($order->getDeliveryCompanyName(), $customerUserUpdateData->deliveryAddressData->companyName);
        $this->assertSame($order->getDeliveryFirstName(), $customerUserUpdateData->deliveryAddressData->firstName);
        $this->assertSame($order->getDeliveryLastName(), $customerUserUpdateData->deliveryAddressData->lastName);
        $this->assertSame($order->getDeliveryTelephone(), $customerUserUpdateData->deliveryAddressData->telephone);
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory
     */
    private function getCustomerUserUpdateDataFactory(): CustomerUserUpdateDataFactory
    {
        return new CustomerUserUpdateDataFactory(
            new BillingAddressDataFactory(),
            new DeliveryAddressDataFactory(),
            new CustomerUserDataFactory(
                $this->createMock(PricingGroupSettingFacade::class),
                $this->createMock(CustomerUserRoleGroupFacade::class),
                $this->createMock(CustomerRepository::class),
                $this->createMock(NewsletterFacade::class),
            ),
        );
    }
}
