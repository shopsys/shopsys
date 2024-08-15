<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Uuid;

/**
 * @ORM\Table(name="sales_representatives")
 * @ORM\Entity
 */
class SalesRepresentative
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected $uuid;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $firstName;

    /**
     * @var string
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $lastName;

    /**
     * @var string
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    protected $telephone;

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     */
    public function __construct(SalesRepresentativeData $salesRepresentativeData)
    {
        $this->uuid = $salesRepresentativeData->uuid ?: Uuid::uuid4()->toString();
        $this->setData($salesRepresentativeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     */
    public function edit(SalesRepresentativeData $salesRepresentativeData): void
    {
        $this->setData($salesRepresentativeData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $salesRepresentativeData
     */
    protected function setData(SalesRepresentativeData $salesRepresentativeData): void
    {
        $this->firstName = $salesRepresentativeData->firstName;
        $this->lastName = $salesRepresentativeData->lastName;
        $this->email = $salesRepresentativeData->email;
        $this->telephone = $salesRepresentativeData->telephone;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }

    /**
     * @return string|null
     */
    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @return string|null
     */
    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    /**
     * @return string
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    /**
     * @return bool
     */
    public function hasNoneOfNamesSet(): bool
    {
        return ($this->getFirstName() === null || $this->getFirstName() === '') && ($this->getLastName() === null || $this->getLastName() === '');
    }
}
