<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressDataFactory implements BillingAddressDataFactoryInterface
{
    public function create(): BillingAddressData
    {
        return new BillingAddressData();
    }

    public function createFromBillingAddress(BillingAddress $billingAddress): BillingAddressData
    {
        $billingAddressData = new BillingAddressData();
        $this->fillFromBillingAddress($billingAddressData, $billingAddress);

        return $billingAddressData;
    }

    protected function fillFromBillingAddress(BillingAddressData $billingAddressData, BillingAddress $billingAddress)
    {
        $billingAddressData->telephone = $billingAddress->getTelephone();
        $billingAddressData->companyCustomer = $billingAddress->isCompanyCustomer();
        $billingAddressData->companyName = $billingAddress->getCompanyName();
        $billingAddressData->companyNumber = $billingAddress->getCompanyNumber();
        $billingAddressData->companyTaxNumber = $billingAddress->getCompanyTaxNumber();
        $billingAddressData->street = $billingAddress->getStreet();
        $billingAddressData->city = $billingAddress->getCity();
        $billingAddressData->postcode = $billingAddress->getPostcode();
        $billingAddressData->country = $billingAddress->getCountry();
    }
}
