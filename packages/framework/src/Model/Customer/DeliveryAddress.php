<?php

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="delivery_addresses")
 * @ORM\Entity
 */
class DeliveryAddress
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
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $companyName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

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

    public function __construct(DeliveryAddressData $deliveryAddressData)
    {
        $this->street = $deliveryAddressData->street;
        $this->city = $deliveryAddressData->city;
        $this->postcode = $deliveryAddressData->postcode;
        $this->companyName = $deliveryAddressData->companyName;
        $this->firstName = $deliveryAddressData->firstName;
        $this->lastName = $deliveryAddressData->lastName;
        $this->telephone = $deliveryAddressData->telephone;
        $this->country = $deliveryAddressData->country;
    }

    public function edit(DeliveryAddressData $deliveryAddressData)
    {
        $this->street = $deliveryAddressData->street;
        $this->city = $deliveryAddressData->city;
        $this->postcode = $deliveryAddressData->postcode;
        $this->companyName = $deliveryAddressData->companyName;
        $this->firstName = $deliveryAddressData->firstName;
        $this->lastName = $deliveryAddressData->lastName;
        $this->telephone = $deliveryAddressData->telephone;
        $this->country = $deliveryAddressData->country;
    }

    public function getCompanyName(): ?string
    {
        return $this->companyName;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getPostcode(): ?string
    {
        return $this->postcode;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function getCountry(): ?\Shopsys\FrameworkBundle\Model\Country\Country
    {
        return $this->country;
    }
}
