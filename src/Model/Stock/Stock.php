<?php

declare(strict_types=1);

namespace App\Model\Stock;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="stocks")
 * @ORM\Entity
 */
class Stock
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
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    protected $domainId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    protected $centralStock;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=true)
     */
    protected $externalId;

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
     * @ORM\Column(type="text", nullable=true)
     */
    protected $openingHours;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $extraordinaryOpeningHours;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $contactText1;

    /**
     * @var string|null
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    protected $contactText2;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $contactInfo;

    //longitude - zeměpisná délka
    //latitude - zeměpisná šířka

    /**
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=16, scale=13, nullable=true)
     */
    protected $locationLat;

    /**
     * @var string|null
     *
     * @ORM\Column(type="decimal", precision=16, scale=13, nullable=true)
     */
    protected $locationLng;

    /**
     * @param \App\Model\Stock\StockData $stockData
     */
    public function __construct(StockData $stockData)
    {
        $this->domainId = $stockData->domainId;
        $this->name = $stockData->name;
        $this->centralStock = $stockData->centralStock;
        $this->externalId = $stockData->externalId;
        $this->street = $stockData->street;
        $this->city = $stockData->city;
        $this->openingHours = $stockData->openingHours;
        $this->extraordinaryOpeningHours = $stockData->extraordinaryOpeningHours;
        $this->contactText1 = $stockData->contactText1;
        $this->contactText2 = $stockData->contactText2;
        $this->contactInfo = $stockData->contactInfo;
        $this->locationLat = $stockData->locationLat;
        $this->locationLng = $stockData->locationLng;
    }

    /**
     * @param \App\Model\Stock\StockData $stockData
     */
    public function edit(StockData $stockData)
    {
        $this->name = $stockData->name;
        $this->centralStock = $stockData->centralStock;
        $this->externalId = $stockData->externalId;
        $this->street = $stockData->street;
        $this->city = $stockData->city;
        $this->openingHours = $stockData->openingHours;
        $this->extraordinaryOpeningHours = $stockData->extraordinaryOpeningHours;
        $this->contactText1 = $stockData->contactText1;
        $this->contactText2 = $stockData->contactText2;
        $this->contactInfo = $stockData->contactInfo;
        $this->locationLat = $stockData->locationLat;
        $this->locationLng = $stockData->locationLng;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isCentralStock(): bool
    {
        return $this->centralStock;
    }

    /**
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
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
    public function getOpeningHours(): ?string
    {
        return $this->openingHours;
    }

    /**
     * @return string|null
     */
    public function getExtraordinaryOpeningHours(): ?string
    {
        return $this->extraordinaryOpeningHours;
    }

    /**
     * @return string|null
     */
    public function getContactText1(): ?string
    {
        return $this->contactText1;
    }

    /**
     * @return string|null
     */
    public function getContactText2(): ?string
    {
        return $this->contactText2;
    }

    /**
     * @return string|null
     */
    public function getContactInfo(): ?string
    {
        return $this->contactInfo;
    }

    /**
     * @return string|null
     */
    public function getLocationLat(): ?string
    {
        return $this->locationLat;
    }

    /**
     * @return string|null
     */
    public function getLocationLng(): ?string
    {
        return $this->locationLng;
    }
}
