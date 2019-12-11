<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

class BillingAddressDataFactory implements BillingAddressDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public function create(): BillingAddressData
    {
        return new BillingAddressData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     *
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public function createForCustomer(Customer $customer): BillingAddressData
    {
        $billingAddressData = $this->create();
        $billingAddressData->customer = $customer;
        return $billingAddressData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddress $billingAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData
     */
    public function createFromBillingAddress(BillingAddress $billingAddress): BillingAddressData
    {
        $billingAddressData = $this->create();
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
    }
}
