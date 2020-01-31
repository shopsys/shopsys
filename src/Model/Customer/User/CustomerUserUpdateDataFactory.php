<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateDataFactory as BaseCustomerUserUpdateDataFactory;

class CustomerUserUpdateDataFactory extends BaseCustomerUserUpdateDataFactory
{
    /**
     * @param \App\Model\Customer\User\RegistrationData $registrationData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserUpdateData
     */
    public function createFromRegistrationData(RegistrationData $registrationData): CustomerUserUpdateData
    {

        /** @var \App\Model\Customer\BillingAddressData $billingAddressData */
        $billingAddressData = $this->billingAddressDataFactory->create();
        $billingAddressData->city = $registrationData->city;
        $billingAddressData->street = $registrationData->street;
        $billingAddressData->postcode = $registrationData->postcode;
        $billingAddressData->companyCustomer = $registrationData->companyCustomer;
        $billingAddressData->companyName = $registrationData->companyName;
        $billingAddressData->companyNumber = $registrationData->companyNumber;
        $billingAddressData->companyTaxNumber = $registrationData->companyTaxNumber;
        $billingAddressData->companyNumberWithVat = $registrationData->companyNumberWithVat;

        /**
         * @var \App\Model\Customer\User\CustomerUserData
         */
        $customerUserData = $this->customerUserDataFactory->createForDomainId($registrationData->domainId);
        $customerUserData->createdAt = $registrationData->createdAt;
        $customerUserData->email = $registrationData->email;
        $customerUserData->lastName = $registrationData->lastName;
        $customerUserData->password = $registrationData->password;
        $customerUserData->firstName = $registrationData->firstName;
        $customerUserData->telephone = $registrationData->telephone;
        $customerUserData->gender = $registrationData->gender;
        $customerUserData->newsletterSubscription = $registrationData->newsletterSubscription;

        $customerUserUpdateData = $this->create();
        $customerUserUpdateData->billingAddressData = $billingAddressData;
        $customerUserUpdateData->customerUserData = $customerUserData;
        $customerUserUpdateData->sendRegistrationMail = true;
        return $customerUserUpdateData;
    }
}
