<?php

namespace Tests\FrameworkBundle\Unit\Model\Customer;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\CustomerData;
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
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public static function getTestCustomerUserData(): CustomerUserData
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

        $billingAddressData = new BillingAddressData();
        $billingAddressData->companyCustomer = true;
        $billingAddressData->companyName = 'CompanyName';
        $billingAddressData->customer = $customerUserData->customer;

        $customerData->billingAddress = new BillingAddress($billingAddressData);
        $customer->edit($customerData);

        return $customerUserData;
    }
}
