<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressDataFactory implements BillingAddressDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    protected function createInstance(): BillingAddressData
    {
        return new BillingAddressData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public function create(): BillingAddressData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public function createFromBillingAddress(BillingAddress $billingAddress): BillingAddressData
    {
        $billingAddressData = $this->createInstance();
        $this->fillFromBillingAddress($billingAddressData, $billingAddress);

        return $billingAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     */
    protected function fillFromBillingAddress(BillingAddressData $billingAddressData, BillingAddress $billingAddress)
    {
        $billingAddressData->companyCustomer = $billingAddress->isCompanyCustomer();
        $billingAddressData->companyName = $billingAddress->getCompanyName();
        $billingAddressData->companyNumber = $billingAddress->getCompanyNumber();
        $billingAddressData->companyTaxNumber = $billingAddress->getCompanyTaxNumber();
        $billingAddressData->street = $billingAddress->getStreet();
        $billingAddressData->city = $billingAddress->getCity();
        $billingAddressData->postcode = $billingAddress->getPostcode();
        $billingAddressData->country = $billingAddress->getCountry();
        $billingAddressData->customer = $billingAddress->getCustomer();
        $billingAddressData->activated = $billingAddress->isActivated();
        $billingAddressData->id = $billingAddress->getId();
    }
}
