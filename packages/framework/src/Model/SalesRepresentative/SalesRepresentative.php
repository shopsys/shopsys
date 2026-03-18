<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Component\Image\Config\Attributes\EntityImage;
use Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData;

#[ORM\Table(name: 'sales_representatives')]
#[ORM\Entity]
#[EntityImage]
class SalesRepresentative
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'guid', unique: true)]
    protected $uuid;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $firstName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    protected $lastName;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $email;

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

    public function __construct(SalesRepresentativeData $salesRepresentativeData)
    {
        $this->uuid = $salesRepresentativeData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($salesRepresentativeData);
    }

    public function edit(SalesRepresentativeData $salesRepresentativeData): void
    {
        $this->setData($salesRepresentativeData);
    }

    protected function setData(SalesRepresentativeData $salesRepresentativeData): void
    {
        $this->firstName = $salesRepresentativeData->firstName;
        $this->lastName = $salesRepresentativeData->lastName;
        $this->email = $salesRepresentativeData->email;
        $this->setTelephoneData($salesRepresentativeData->telephone);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
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
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getTelephone()
    {
        return $this->getTelephoneData()?->toPhoneNumber();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\PhonePrefix\PhoneData|null
     */
    public function getTelephoneData()
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

            return;
        }

        $this->telephonePrefix = $phoneData->prefix;
        $this->telephonePrefixCountryCode = $phoneData->countryCode;
        $this->telephoneNumber = $phoneData->number;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return bool
     */
    public function hasNoneOfNamesSet()
    {
        return ($this->getFirstName() === null || $this->getFirstName() === '') && ($this->getLastName() === null || $this->getLastName() === '');
    }

    /**
     * @return string
     */
    public function getPresentationalLabel()
    {
        return $this->hasNoneOfNamesSet() ? (string)$this->getId() : $this->getFullName();
    }
}
