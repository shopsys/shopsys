<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Component\EntityLog\Attribute\LoggableChild;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress as BaseBillingAddress;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

/**
 * @method void setData(\App\Model\Customer\BillingAddressData $billingAddressData)
 * @method void edit(\App\Model\Customer\BillingAddressData $billingAddressData)
 * @method __construct(\App\Model\Customer\BillingAddressData $billingAddressData)
 */
#[AsMcpTable]
#[LoggableChild]
#[ORM\Table(name: 'billing_addresses')]
#[ORM\Entity]
class BillingAddress extends BaseBillingAddress
{
}
