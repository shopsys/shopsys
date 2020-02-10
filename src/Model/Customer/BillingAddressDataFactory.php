<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData as BaseBillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory as BaseBillingAddressDataFactory;

class BillingAddressDataFactory extends BaseBillingAddressDataFactory
{
    /**
     * @return \App\Model\Customer\BillingAddressData
     */
    public function create(): BaseBillingAddressData
    {
        return new BillingAddressData();
    }
}
