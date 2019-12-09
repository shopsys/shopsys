<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface UserFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\UserData $userData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\User
     */
    public function create(UserData $userData, ?DeliveryAddress $deliveryAddress): User;
}
