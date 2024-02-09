<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="store_opening_hours_ranges")
 */
class OpeningHoursRange
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
     * @ORM\Column(type="string", length=5)
     */
    protected $openingTime;

    /**
     * @var string
     * @ORM\Column(type="string", length=5)
     */
    protected $closingTime;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours
     * @ORM\ManyToOne(targetEntity="Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours", inversedBy="openingHoursRanges")
     * @ORM\JoinColumn(name="opening_hours_id", referencedColumnName="id", nullable=false)
     */
    protected $openingHours;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRangeData $openingHoursRangeData
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours $openingHours
     */
    public function __construct(OpeningHoursRangeData $openingHoursRangeData, OpeningHours $openingHours)
    {
        $this->openingHours = $openingHours;
        $this->openingTime = $openingHoursRangeData->openingTime;
        $this->closingTime = $openingHoursRangeData->closingTime;
    }

    /**
     * @return string
     */
    public function getOpeningTime()
    {
        return $this->openingTime;
    }

    /**
     * @return string
     */
    public function getClosingTime()
    {
        return $this->closingTime;
    }
}
