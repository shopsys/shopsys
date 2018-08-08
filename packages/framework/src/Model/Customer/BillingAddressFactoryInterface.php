<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface BillingAddressFactoryInterface
{

    public function create(BillingAddressData $data): BillingAddress;
}
