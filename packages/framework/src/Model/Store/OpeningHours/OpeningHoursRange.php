<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'store_opening_hours_ranges')]
#[ORM\Entity]
class OpeningHoursRange
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
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 5)]
    protected $openingTime;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 5)]
    protected $closingTime;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours
     */
    #[AsMcpColumn]
    #[ORM\JoinColumn(name: 'opening_hours_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: OpeningHours::class, inversedBy: 'openingHoursRanges')]
    protected $openingHours;

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
