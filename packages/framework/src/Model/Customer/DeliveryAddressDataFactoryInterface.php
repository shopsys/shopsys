<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

interface DeliveryAddressDataFactoryInterface
{
    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public function create(): DeliveryAddressData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress $deliveryAddress
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public function createFromDeliveryAddress(DeliveryAddress $deliveryAddress): DeliveryAddressData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData
     */
    public function createForCustomer(Customer $customer): DeliveryAddressData;
}
