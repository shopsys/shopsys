<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'billing_addresses')]
#[ORM\Entity]
class BillingAddress
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'billingAddresses')]
    protected $customer;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean', nullable: false)]
    protected $companyCustomer;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $companyName;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    protected $companyTaxNumber;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $street;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $city;

    /**
     * @var string|null
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $postcode;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected $country;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $activated;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid {
        set {
            $this->uuid = $value ?: Uuid::uuid4()->toString();
        }
    }

    public function __construct(BillingAddressData $billingAddressData)
    {
        $this->customer = $billingAddressData->customer;
        $this->activated = $billingAddressData->activated;
        $this->uuid = $billingAddressData->uuid;
        $this->setData($billingAddressData);
    }

    public function edit(BillingAddressData $billingAddressData): void
    {
        $this->setData($billingAddressData);
    }

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
    public function getId()
    {
        return $this->id;
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
     * @return \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @return bool
     */
    public function isActivated()
    {
        return $this->activated;
    }

    public function activate(): void
    {
        $this->activated = true;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }
}
