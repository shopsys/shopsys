<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface UserFactoryInterface
{

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress|null $deliveryAddress
     */
    public function create(
        UserData $userData,
        BillingAddress $billingAddress,
        ?DeliveryAddress $deliveryAddress
    ): User;
}
