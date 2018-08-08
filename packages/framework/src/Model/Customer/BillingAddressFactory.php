<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressFactory implements BillingAddressFactoryInterface
{

    public function create(BillingAddressData $data): BillingAddress
    {
        return new BillingAddress($data);
    }
}
