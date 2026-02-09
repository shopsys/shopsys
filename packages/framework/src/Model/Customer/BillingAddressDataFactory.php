<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressDataFactory
{
    protected function createInstance(): BillingAddressData
    {
        return new BillingAddressData();
    }

    public function create(): BillingAddressData
    {
        return $this->createInstance();
    }

    public function createFromBillingAddress(BillingAddress $billingAddress): BillingAddressData
    {
        $billingAddressData = $this->createInstance();
        $this->fillFromBillingAddress($billingAddressData, $billingAddress);

        return $billingAddressData;
    }

    protected function fillFromBillingAddress(
        BillingAddressData $billingAddressData,
        BillingAddress $billingAddress,
    ): void {
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
        $billingAddressData->uuid = $billingAddress->getUuid();
    }
}
