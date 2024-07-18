<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory as BaseBillingAddressDataFactory;

/**
 * @method \App\Model\Customer\BillingAddressData createFromBillingAddress(\App\Model\Customer\BillingAddress $billingAddress)
 * @method \App\Model\Customer\BillingAddressData create()
 * @method fillFromBillingAddress(\App\Model\Customer\BillingAddressData $billingAddressData, \App\Model\Customer\BillingAddress $billingAddress)
 */
class BillingAddressDataFactory extends BaseBillingAddressDataFactory
{
    /**
     * @return \App\Model\Customer\BillingAddressData
     */
    protected function createInstance(): BillingAddressData
    {
        return new BillingAddressData();
    }
}
