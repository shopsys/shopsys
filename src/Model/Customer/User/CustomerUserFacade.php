<?php

declare(strict_types=1);


namespace App\Model\Customer\User;


use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;

class CustomerUserFacade extends BaseCustomerUserFacade
{

    /**
     * @param \App\Model\Customer\User\CustomerUserData $customerUserData
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     * @throws \Shopsys\FrameworkBundle\Model\Customer\Exception\DuplicateEmailException
     */
    public function registerWithBillingAddress(CustomerUserData $customerUserData)
    {

        $billingAddress = $this->billingAddressDataFactory->create();
        $billingAddress->city = $customerUserData->city;
        $billingAddress->street = $customerUserData->street;
        $billingAddress->postcode = $customerUserData->postcode;

        $customer = $this->customerFacade->createCustomerWithBillingAddress($billingAddress);
        $customerUserData->customer = $customer;

        $deliveryAddress = null;

        $customerUser = $this->customerUserFactory->create($customerUserData, $deliveryAddress);
        $this->setEmail($customerUserData->email, $customerUser);

        $this->em->persist($customerUser);
        $this->em->flush();

        $this->customerMailFacade->sendRegistrationMail($customerUser);
        return $customerUser;
    }

}