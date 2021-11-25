<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData as BaseBillingAddressData;

class BillingAddressData extends BaseBillingAddressData
{
    /**
     * @var bool
     */
    public $activated = true;
}
