<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\Customer;

interface CustomerUserDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function create(): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomer(Customer $customer): CustomerUserData;

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForDomainId(int $domainId): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser $customerUser
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData
     */
    public function createForCustomerWithPresetPricingGroup(Customer $customer): CustomerUserData;
}
