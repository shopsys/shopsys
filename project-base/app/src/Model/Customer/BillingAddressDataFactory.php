<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData as BaseBillingAddressData;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressDataFactory as BaseBillingAddressDataFactory;

/**
 * @method \App\Model\Customer\BillingAddressData createFromBillingAddress(\App\Model\Customer\BillingAddress $billingAddress)
 * @method \App\Model\Customer\BillingAddressData create()
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

    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     * @param \App\Model\Customer\BillingAddress $billingAddress
     */
    protected function fillFromBillingAddress(
        BaseBillingAddressData $billingAddressData,
        BillingAddress $billingAddress,
    ): void {
        parent::fillFromBillingAddress($billingAddressData, $billingAddress);

        $billingAddressData->activated = $billingAddress->isActivated();
    }
}
