<?php

declare(strict_types=1);

namespace App\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData as BaseUserData;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserDataFactory as BaseUserDataFactory;

/**
 * @method \App\Model\Customer\User\CustomerUserData create()
 * @method \App\Model\Customer\User\CustomerUserData createForCustomer(\Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Customer\User\CustomerUserData createForDomainId(int $domainId)
 * @method \App\Model\Customer\User\CustomerUserData createFromCustomerUser(\App\Model\Customer\User\CustomerUser $customerUser)
 * @method fillForDomainId(\App\Model\Customer\User\CustomerUserData $customerUserData, int $domainId)
 * @method fillFromUser(\App\Model\Customer\User\CustomerUserData $customerUserData, \App\Model\Customer\User\CustomerUser $customerUser)
 */
class CustomerUserDataFactory extends BaseUserDataFactory
{
    /**
     * @return \App\Model\Customer\User\CustomerUserData
     */
    protected function createInstance(): BaseUserData
    {
        return new CustomerUserData();
    }
}
