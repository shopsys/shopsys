<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerDataFactory
{
    protected function createInstance(): CustomerData
    {
        return new CustomerData();
    }

    public function create(): CustomerData
    {
        return $this->createInstance();
    }

    public function createForDomain(int $domainId): CustomerData
    {
        $customerData = $this->createInstance();
        $customerData->domainId = $domainId;

        return $customerData;
    }

    public function createFromCustomer(Customer $customer): CustomerData
    {
        $customerData = $this->createInstance();
        $customerData->billingAddress = $customer->getBillingAddress();
        $customerData->deliveryAddresses = $customer->getDeliveryAddresses();
        $customerData->domainId = $customer->getDomainId();

        return $customerData;
    }
}
