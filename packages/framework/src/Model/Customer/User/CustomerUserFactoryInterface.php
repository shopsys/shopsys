<?php

namespace Shopsys\FrameworkBundle\Model\Customer\User;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser;
use Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData;

interface CustomerUserFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\User\CustomerUser
     */
    public function create(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress): CustomerUser;
}
