<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface UserFactoryInterface
{

    public function create(
        UserData $userData,
        BillingAddress $billingAddress,
        ?DeliveryAddress $deliveryAddress
    ): User;
}
