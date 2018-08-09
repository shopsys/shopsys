<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="billing_addresses")
 * @ORM\Entity
 */
class BillingAddress
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $companyCustomer;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    protected $companyTaxNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $street;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $city;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $postcode;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Country\Country")
     * @ORM\JoinColumn(name="country_id", referencedColumnName="id", nullable=true)
     */
    protected $country;

    public function __construct(BillingAddressData $billingAddressData)
    {
        $this->street = $billingAddressData->street;
        $this->city = $billingAddressData->city;
        $this->postcode = $billingAddressData->postcode;
        $this->companyCustomer = $billingAddressData->companyCustomer;
        if ($this->companyCustomer) {
            $this->companyName = $billingAddressData->companyName;
            $this->companyNumber = $billingAddressData->companyNumber;
            $this->companyTaxNumber = $billingAddressData->companyTaxNumber;
        }
        $this->telephone = $billingAddressData->telephone;
        $this->country = $billingAddressData->country;
    }

    public function edit(BillingAddressData $billingAddressData)
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
        $this->telephone = $billingAddressData->telephone;
        $this->country = $billingAddressData->country;
    }

    /**
     * @return bool
     */
    public function isCompanyCustomer()
    {
        return $this->companyCustomer;
    }

    /**
     * @return string|null
     */
    public function getCompanyName()
    {
        return $this->companyName;
    }

    /**
     * @return string|null
     */
    public function getCompanyNumber()
    {
        return $this->companyNumber;
    }

    /**
     * @return string|null
     */
    public function getCompanyTaxNumber()
    {
        return $this->companyTaxNumber;
    }

    /**
     * @return string|null
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @return string|null
     */
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }
}
