<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddress;
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

    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     * @param \App\Model\Customer\BillingAddress $billingAddress
     */
    protected function fillFromBillingAddress(BaseBillingAddressData $billingAddressData, BillingAddress $billingAddress): void
    {
        parent::fillFromBillingAddress($billingAddressData, $billingAddress);
        $billingAddressData->companyNumberWithVat = $billingAddress->getCompanyNumberWithVat();
    }
}
