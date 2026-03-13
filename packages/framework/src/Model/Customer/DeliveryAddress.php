<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Customer;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Country\Country;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

#[ORM\Table(name: 'delivery_addresses')]
#[ORM\Entity]
class DeliveryAddress
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Customer\Customer
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'deliveryAddresses')]
    protected $customer;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $companyName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $firstName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $lastName;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $street;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $city;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $postcode;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    protected $telephonePrefix;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 2, nullable: true)]
    protected $telephonePrefixCountryCode;

    /**
     * @var string|null
     */
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    protected $telephoneNumber;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Country\Country|null
     */
    #[ORM\JoinColumn(name: 'country_id', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    protected $country;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    public function __construct(DeliveryAddressData $deliveryAddressData)
    {
        $this->customer = $deliveryAddressData->customer;
        $this->uuid = $deliveryAddressData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($deliveryAddressData);
    }

    public function edit(DeliveryAddressData $deliveryAddressData): void
    {
        $this->setData($deliveryAddressData);
    }

    protected function setData(DeliveryAddressData $deliveryAddressData): void
    {
        $this->street = $deliveryAddressData->street;
        $this->city = $deliveryAddressData->city;
        $this->postcode = $deliveryAddressData->postcode;
        $this->companyName = $deliveryAddressData->companyName;
        $this->firstName = $deliveryAddressData->firstName;
        $this->lastName = $deliveryAddressData->lastName;
        $this->setTelephoneData($deliveryAddressData->telephone);
        $this->country = $deliveryAddressData->country;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName()
    {
        return $this->lastName;
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

    public function getTelephone(): ?string
    {
        return $this->getTelephoneData()?->toPhoneNumber();
    }

    public function getTelephoneData(): ?PhoneData
    {
        if ($this->telephoneNumber === null) {
            return null;
        }

        return new PhoneData(
            $this->telephonePrefixCountryCode,
            $this->telephonePrefix,
            $this->telephoneNumber,
        );
    }

    public function setTelephoneData(?PhoneData $phoneData): void
    {
        if ($phoneData === null) {
            $this->telephonePrefix = null;
            $this->telephonePrefixCountryCode = null;
            $this->telephoneNumber = null;
        } else {
            $this->telephonePrefix = $phoneData->prefix;
            $this->telephonePrefixCountryCode = $phoneData->countryCode;
            $this->telephoneNumber = $phoneData->number;
        }
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
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    public function getFullAddress(): string
    {
        return $this->street . ', ' . $this->city . ', ' . $this->postcode;
    }
}
