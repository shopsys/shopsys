<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

class CustomerDataFactory implements CustomerDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    public function create(): CustomerData
    {
        return new CustomerData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\CustomerData
     */
    public function createFromCustomer(Customer $customer): CustomerData
    {
        $customerData = $this->create();
        $customerData->billingAddress = $customer->getBillingAddress();
        $customerData->deliveryAddresses = $customer->getDeliveryAddresses();

        return $customerData;
    }
}
