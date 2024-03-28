<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository as BaseDeliveryAddressRepository;
use Shopsys\FrameworkBundle\Model\Customer\Exception\DeliveryAddressNotFoundException;

/**
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 * @method \App\Model\Customer\DeliveryAddress|null findByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 */
class DeliveryAddressRepository extends BaseDeliveryAddressRepository
{
    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\DeliveryAddress
     */
    public function getByUuidAndCustomer(string $uuid, Customer $customer): DeliveryAddress
    {
        $deliveryAddress = $this->findByUuidAndCustomer($uuid, $customer);

        if ($deliveryAddress === null) {
            throw new DeliveryAddressNotFoundException(
                'Delivery address with UUID ' . $uuid . ' not found.',
            );
        }

        return $deliveryAddress;
    }
}
