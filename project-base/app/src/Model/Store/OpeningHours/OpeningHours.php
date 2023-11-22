<?php

declare(strict_types=1);

namespace App\Model\Store\OpeningHours;

use App\Model\Store\Store;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_opening_hours")
 */
class OpeningHours
{
    /**
     * @var int
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @var \App\Model\Store\Store
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="\App\Model\Store\Store", inversedBy="openingHours")
     */
    protected $store;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    protected $dayOfWeek;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $firstOpeningTime;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $firstClosingTime;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $secondOpeningTime;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $secondClosingTime;

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    public function __construct(OpeningHoursData $openingHourData)
    {
        $this->setData($openingHourData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Store\Store
     */
    public function getStore(): \App\Model\Store\Store
    {
        return $this->store;
    }

    /**
     * @return int
     */
    public function getDayOfWeek(): int
    {
        return $this->dayOfWeek;
    }

    /**
     * @param \App\Model\Store\Store $store
     */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * @return string|null
     */
    public function getFirstOpeningTime(): ?string
    {
        return $this->firstOpeningTime;
    }

    /**
     * @return string|null
     */
    public function getFirstClosingTime(): ?string
    {
        return $this->firstClosingTime;
    }

    /**
     * @return string|null
     */
    public function getSecondOpeningTime(): ?string
    {
        return $this->secondOpeningTime;
    }

    /**
     * @return string|null
     */
    public function getSecondClosingTime(): ?string
    {
        return $this->secondClosingTime;
    }

    /**
     * @param \App\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    protected function setData(OpeningHoursData $openingHourData): void
    {
        $this->dayOfWeek = $openingHourData->dayOfWeek;
        $this->firstOpeningTime = $openingHourData->firstOpeningTime;
        $this->firstClosingTime = $openingHourData->firstClosingTime;
        $this->secondOpeningTime = $openingHourData->secondOpeningTime;
        $this->secondClosingTime = $openingHourData->secondClosingTime;
    }
}
