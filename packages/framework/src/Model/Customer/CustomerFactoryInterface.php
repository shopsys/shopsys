<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

interface CustomerFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\CustomerData $customerData
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function create(CustomerData $customerData): Customer;
}
