<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class DeliveryAddressFactory implements DeliveryAddressFactoryInterface
{

    public function create(DeliveryAddressData $data): DeliveryAddress
    {
        return new DeliveryAddress($data);
    }
}
