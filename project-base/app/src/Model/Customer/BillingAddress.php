<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress as BaseBillingAddress;

/**
 * @ORM\Table(name="billing_addresses")
 * @ORM\Entity
 * @method setData(\App\Model\Customer\BillingAddressData $billingAddressData)
 * @method edit(\App\Model\Customer\BillingAddressData $billingAddressData)
 * @method __construct(\App\Model\Customer\BillingAddressData $billingAddressData)
 */
class BillingAddress extends BaseBillingAddress
{
}
