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
    protected $openingTime;

    /**
     * @var string|null
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    protected $closingTime;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    public function __construct(OpeningHoursData $openingHourData)
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
    public function setStore($store): void
    {
        $this->store = $store;
    }

    /**
     * @return string|null
     */
    public function getOpeningTime()
    {
        return $this->openingTime;
    }

    /**
     * @return string|null
     */
    public function getClosingTime()
    {
        return $this->closingTime;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $openingHourData
     */
    protected function setData(OpeningHoursData $openingHourData): void
    {
        $this->dayOfWeek = $openingHourData->dayOfWeek;
        $this->openingTime = $openingHourData->openingTime;
        $this->closingTime = $openingHourData->closingTime;
    }
}
