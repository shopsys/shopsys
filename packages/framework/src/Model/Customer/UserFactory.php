<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class UserFactory implements UserFactoryInterface
{

    public function create(
        UserData $userData,
        BillingAddress $billingAddress,
        ?DeliveryAddress $deliveryAddress
    ): User {
        return new User($userData, $billingAddress, $deliveryAddress);
    }
}
