<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface CustomerUserFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerUserData $customerUserData
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerUser
     */
    public function create(CustomerUserData $customerUserData, ?DeliveryAddress $deliveryAddress): CustomerUser;
}
