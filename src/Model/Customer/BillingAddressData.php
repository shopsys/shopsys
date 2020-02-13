<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData as BaseBillingAddressData;

class BillingAddressData extends BaseBillingAddressData
{
    /**
     * @var string|null
     */
    public $companyNumberWithVat;
}
