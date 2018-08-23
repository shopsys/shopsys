<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

interface BillingAddressFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $data
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddress
     */
    public function create(BillingAddressData $data): BillingAddress;
}
