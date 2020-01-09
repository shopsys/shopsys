<?php

declare(strict_types=1);


namespace App\Model\Customer\User;


use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserFacade as BaseCustomerUserFacade;

class CustomerUserFacade extends BaseCustomerUserFacade
{

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function register(CustomerUserData $customerUserData)
    {
        $billingAddress = $this->billingAddressDataFactory->create();

        $customer = $this->customerFacade->createCustomerWithBillingAddress($billingAddress);
        $customerUser = $this->createCustomerUser($customer, $customerUserData);

        $this->customerMailFacade->sendRegistrationMail($customerUser);

        return $customerUser;
    }

}