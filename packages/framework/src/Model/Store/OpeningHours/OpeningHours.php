<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Store\Store;

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
     * @var \Shopsys\FrameworkBundle\Model\Store\Store
     * @ORM\JoinColumn(nullable=false)
     * @ORM\ManyToOne(targetEntity="\Shopsys\FrameworkBundle\Model\Store\Store", inversedBy="openingHours")
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
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    public function __construct(OpeningHoursData $openingHourData)
    {
        $this->setData($openingHourData);
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    public function edit(OpeningHoursData $openingHourData): void
    {
        $this->setData($openingHourData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return int
     */
    public function getDayOfWeek()
    {
        return $this->dayOfWeek;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     */
    public function setStore(Store $store): void
    {
        $this->store = $store;
    }

    /**
     * @return string|null
     */
    public function getFirstOpeningTime()
    {
        return $this->firstOpeningTime;
    }

    /**
     * @return string|null
     */
    public function getFirstClosingTime()
    {
        return $this->firstClosingTime;
    }

    /**
     * @return string|null
     */
    public function getSecondOpeningTime()
    {
        return $this->secondOpeningTime;
    }

    /**
     * @return string|null
     */
    public function getSecondClosingTime()
    {
        return $this->secondClosingTime;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $openingHourData
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
