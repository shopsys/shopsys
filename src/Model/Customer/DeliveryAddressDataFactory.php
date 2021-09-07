<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress as BaseDeliveryAddress;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData as BaseDeliveryAddressData;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory as BaseDeliveryAddressDataFactory;

/**
 * @method \App\Model\Customer\DeliveryAddressData create()
 * @method \App\Model\Customer\DeliveryAddressData createFromDeliveryAddress(\App\Model\Customer\DeliveryAddress $deliveryAddress)
 */
class DeliveryAddressDataFactory extends BaseDeliveryAddressDataFactory
{
    /**
     * @return \App\Model\Customer\DeliveryAddressData
     */
    protected function createInstance(): DeliveryAddressData
    {
        return new DeliveryAddressData();
    }

    /**
     * @param \App\Model\Customer\DeliveryAddressData $deliveryAddressData
     * @param \App\Model\Customer\DeliveryAddress $deliveryAddress
     */
    protected function fillFromDeliveryAddress(BaseDeliveryAddressData $deliveryAddressData, BaseDeliveryAddress $deliveryAddress)
    {
        parent::fillFromDeliveryAddress($deliveryAddressData, $deliveryAddress);

        $deliveryAddressData->uuid = $deliveryAddress->getUuid();
    }
}
