<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressRepository as BaseDeliveryAddressRepository;

/**
 * @method \App\Model\Customer\DeliveryAddress getById(int $deliveryAddressId)
 * @method \App\Model\Customer\DeliveryAddress|null findByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 * @method \App\Model\Customer\DeliveryAddress getByUuidAndCustomer(string $uuid, \Shopsys\FrameworkBundle\Model\Customer\Customer $customer)
 */
class DeliveryAddressRepository extends BaseDeliveryAddressRepository
{
}
