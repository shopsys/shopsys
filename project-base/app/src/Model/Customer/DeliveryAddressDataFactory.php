<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressDataFactory as BaseDeliveryAddressDataFactory;

/**
 * @method \App\Model\Customer\DeliveryAddressData create()
 * @method \App\Model\Customer\DeliveryAddressData createFromDeliveryAddress(\App\Model\Customer\DeliveryAddress $deliveryAddress)
 * @method fillFromDeliveryAddress(\App\Model\Customer\DeliveryAddressData $deliveryAddressData, \App\Model\Customer\DeliveryAddress $deliveryAddress)
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
}
