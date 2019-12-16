<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Model\Order\Order;

interface CustomerUserUpdateDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData
     */
    public function create(): CustomerUserUpdateData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUser $customerUser
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData
     */
    public function createFromCustomerUser(CustomerUser $customerUser): CustomerUserUpdateData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUser $customerUser
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData
     */
    public function createAmendedByOrder(CustomerUser $customerUser, Order $order): CustomerUserUpdateData;
}
