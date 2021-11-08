<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\Customer;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository as BaseDeliveryAddressRepository;

/**
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 */
class DeliveryAddressRepository extends BaseDeliveryAddressRepository
{
    /**
     * @param string $uuid
     * @param \Shopsys\FrameworkBundle\Model\Customer\Customer $customer
     * @return \App\Model\Customer\DeliveryAddress|null
     */
    public function findByUuidAndCustomer(string $uuid, Customer $customer): ?DeliveryAddress
    {
        return $this->getDeliveryAddressRepository()->findOneBy(
            [
                'uuid' => $uuid,
                'customer' => $customer,
            ]
        );
    }
}
