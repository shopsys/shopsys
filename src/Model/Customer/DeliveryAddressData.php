<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\DeliveryAddressData as BaseDeliveryAddressData;

class DeliveryAddressData extends BaseDeliveryAddressData
{
    public ?string $uuid = null;
}
