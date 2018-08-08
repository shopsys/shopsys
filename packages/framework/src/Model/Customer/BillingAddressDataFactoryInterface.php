<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface BillingAddressDataFactoryInterface
{
    public function create(): BillingAddressData;

    public function createFromBillingAddress(BillingAddress $billingAddress): BillingAddressData;
}
