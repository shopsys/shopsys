<?php

declare(strict_types=1);

namespace App\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddress as BaseBillingAddress;
use Shopsys\FrameworkBundle\Model\Customer\BillingAddressData;

/**
 * @ORM\Table(name="billing_addresses")
 * @ORM\Entity
 */
class BillingAddress extends BaseBillingAddress
{
    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyNumberWithVat;

    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     */
    public function __construct(BillingAddressData $billingAddressData)
    {
        parent::__construct($billingAddressData);
        if ($this->companyCustomer) {
            $this->companyNumberWithVat = $billingAddressData->companyNumberWithVat;
        }
    }

    /**
     * @param \App\Model\Customer\BillingAddressData $billingAddressData
     */
    public function edit(BillingAddressData $billingAddressData): void
    {
        parent::edit($billingAddressData);
        if ($this->companyCustomer) {
            $this->companyNumberWithVat = $billingAddressData->companyNumberWithVat;
        }
    }

    /**
     * @return string|null
     */
    public function getCompanyNumberWithVat(): ?string
    {
        return $this->companyNumberWithVat;
    }
}
