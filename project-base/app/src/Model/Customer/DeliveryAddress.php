<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress as BaseDeliveryAddress;

/**
 * @method edit(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method setData(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method __construct(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 */
#[ORM\Table(name: 'delivery_addresses')]
#[ORM\Entity]
class DeliveryAddress extends BaseDeliveryAddress
{
}
