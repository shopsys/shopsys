<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddress as BaseDeliveryAddress;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method void edit(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method void setData(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 * @method __construct(\App\Model\Customer\DeliveryAddressData $deliveryAddressData)
 */
#[AsMcpTable]
#[ORM\Table(name: 'delivery_addresses')]
#[ORM\Entity]
class DeliveryAddress extends BaseDeliveryAddress
{
}
