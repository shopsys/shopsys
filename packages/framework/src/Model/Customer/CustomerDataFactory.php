<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerDataFactory implements CustomerDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    protected function createInstance(): CustomerData
    {
        return new CustomerData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    public function create(): CustomerData
    {
        return $this->createInstance();
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    public function createForDomain(int $domainId): CustomerData
    {
        $customerData = $this->createInstance();
        $customerData->domainId = $domainId;

        return $customerData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    public function createFromCustomer(Customer $customer): CustomerData
    {
        $customerData = $this->createInstance();
        $customerData->billingAddress = $customer->getBillingAddress();
        $customerData->deliveryAddresses = $customer->getDeliveryAddresses();
        $customerData->domainId = $customer->getDomainId();

        return $customerData;
    }
}
