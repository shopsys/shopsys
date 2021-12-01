<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress as BaseBillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;

/**
 * @ORM\Table(name="billing_addresses")
 * @ORM\Entity
 * @method setData(\App\Model\Customer\BillingAddressData $billingAddressData)
 * @method edit(\App\Model\Customer\BillingAddressData $billingAddressData)
 */
class BillingAddress extends BaseBillingAddress
{
    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private bool $activated;

    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     */
    public function __construct(BillingAddressData $billingAddressData)
    {
        parent::__construct($billingAddressData);

        $this->activated = $billingAddressData->activated;
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function activate(): void
    {
        $this->activated = true;
    }
}
