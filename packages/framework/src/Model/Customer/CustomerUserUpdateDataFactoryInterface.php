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
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData
     */
    public function createFromUser(User $user): CustomerUserUpdateData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserUpdateData
     */
    public function createAmendedByOrder(User $user, Order $order): CustomerUserUpdateData;
}
