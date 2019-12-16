<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface CustomerUserDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function create(): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createForCustomer(Customer $customer): CustomerUserData;

    /**
     * @param int $domainId
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createForDomainId(int $domainId): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserData;
}
