<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use DateTime;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\Country\CountryData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroup;
use Shopsys\FrameworkBundle\Model\Pricing\Group\PricingGroupData;

class TestCustomerProvider
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public static function getTestCustomerUser(): CustomerUser
    {
        return new CustomerUser(self::getTestCustomerUserData());
    }

    /**
     * @param bool $isCompany
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public static function getTestCustomerUserData(bool $isCompany = true): CustomerUserData
    {
        $pricingGroupData = new PricingGroupData();
        $pricingGroupData->name = 'name';
        $pricingGroup = new PricingGroup($pricingGroupData, Domain::FIRST_DOMAIN_ID);

        $customerData = new CustomerData();
        $customerData->domainId = Domain::FIRST_DOMAIN_ID;
        $customer = new Customer($customerData);

        $customerUserData = new CustomerUserData();
        $customerUserData->firstName = 'Firstname';
        $customerUserData->lastName = 'Lastname';
        $customerUserData->email = 'no-reply@shopsys.com';
        $customerUserData->password = 'pa55w0rd';
        $customerUserData->domainId = Domain::FIRST_DOMAIN_ID;
        $customerUserData->customer = $customer;
        $customerUserData->pricingGroup = $pricingGroup;
        $customerUserData->createdAt = new DateTime();

        $billingAddressData = self::getBillingAddressData($customer, $isCompany);
        $customerData->billingAddress = new BillingAddress($billingAddressData);

        $deliveryAddressData = self::getDeliveryAddressData($customer);
        $customerData->deliveryAddresses = [new DeliveryAddress($deliveryAddressData)];

        $customer->edit($customerData);

        return $customerUserData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @param bool $isCompany
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public static function getBillingAddressData(Customer $customer, bool $isCompany = true): BillingAddressData
    {
        $billingCountryData = new CountryData();
        $billingCountryData->names = ['cs' => 'Česká republika'];
        $billingCountry = new Country($billingCountryData);
        $billingAddressData = new BillingAddressData();
        $billingAddressData->street = 'street';
        $billingAddressData->city = 'city';
        $billingAddressData->postcode = 'postcode';
        $billingAddressData->country = $billingCountry;
        $billingAddressData->customer = $customer;

        if ($isCompany) {
            $billingAddressData->companyCustomer = true;
            $billingAddressData->companyName = 'companyName';
            $billingAddressData->companyNumber = 'companyNumber';
            $billingAddressData->companyTaxNumber = 'companyTaxNumber';
        }

        return $billingAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public static function getDeliveryAddressData(Customer $customer): DeliveryAddressData
    {
        $deliveryCountryData = new CountryData();
        $deliveryCountryData->names = ['cs' => 'Slovenská republika'];
        $deliveryCountry = new Country($deliveryCountryData);
        $deliveryAddressData = new DeliveryAddressData();
        $deliveryAddressData->addressFilled = false;
        $deliveryAddressData->street = 'deliveryStreet';
        $deliveryAddressData->city = 'deliveryCity';
        $deliveryAddressData->postcode = 'deliveryPostcode';
        $deliveryAddressData->companyName = 'deliveryCompanyName';
        $deliveryAddressData->firstName = 'deliveryFirstName';
        $deliveryAddressData->lastName = 'deliveryLastName';
        $deliveryAddressData->telephone = 'deliveryTelephone';
        $deliveryAddressData->country = $deliveryCountry;
        $deliveryAddressData->customer = $customer;
        return $deliveryAddressData;
    }
}
