<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Country\Country;

/**
 * @ORM\Table(name="billing_addresses")
 * @ORM\Entity
 */
class BillingAddress
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Customer\Customer", inversedBy="billingAddresses")
     * @ORM\JoinColumn(nullable=false)
     */
    protected $customer;

    /**
     * @var bool
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $companyCustomer;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $street;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $city;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $country;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     */
    public function __construct(BillingAddressData $billingAddressData)
    {
        $this->customer = $billingAddressData->customer;
        $this->setData($billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     */
    public function edit(BillingAddressData $billingAddressData): void
    {
        $this->setData($billingAddressData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Customer\BillingAddressData $billingAddressData
     */
    protected function setData(BillingAddressData $billingAddressData): void
    {
        $this->street = $billingAddressData->street;
        $this->city = $billingAddressData->city;
        $this->postcode = $billingAddressData->postcode;
        $this->companyCustomer = $billingAddressData->companyCustomer;
        if ($this->companyCustomer) {
            $this->companyName = $billingAddressData->companyName;
            $this->companyNumber = $billingAddressData->companyNumber;
            $this->companyTaxNumber = $billingAddressData->companyTaxNumber;
        } else {
            $this->companyName = null;
            $this->companyNumber = null;
            $this->companyTaxNumber = null;
        }
        $this->country = $billingAddressData->country;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isCompanyCustomer(): bool
    {
        return $this->companyCustomer;
    }

    /**
     * @return string|null
     */
    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    /**
     * @return string|null
     */
    public function getCompanyNumber(): ?string
    {
        return $this->companyNumber;
    }

    /**
     * @return string|null
     */
    public function getCompanyTaxNumber(): ?string
    {
        return $this->companyTaxNumber;
    }

    /**
     * @return string|null
     */
    public function getStreet(): ?string
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer(): Customer
    {
        return $this->customer;
    }
}
