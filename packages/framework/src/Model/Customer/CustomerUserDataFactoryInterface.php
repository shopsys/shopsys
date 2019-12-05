<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Shopsys\FrameworkBundle\Model\Order\Order;

interface CustomerUserDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function create(): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createFromUser(User $user): CustomerUserData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User $user
     * @param \Shopsys\FrameworkBundle\Model\Order\Order $order
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData
     */
    public function createAmendedByOrder(User $user, Order $order): CustomerUserData;
}
