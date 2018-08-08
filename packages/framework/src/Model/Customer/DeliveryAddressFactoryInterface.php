<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface DeliveryAddressFactoryInterface
{

    public function create(DeliveryAddressData $data): DeliveryAddress;
}
