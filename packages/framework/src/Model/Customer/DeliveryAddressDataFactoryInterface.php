<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface DeliveryAddressDataFactoryInterface
{
    public function create(): DeliveryAddressData;

    public function createFromDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressData;
}
