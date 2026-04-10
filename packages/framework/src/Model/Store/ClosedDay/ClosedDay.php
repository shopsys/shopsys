<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Store\Store;
use Shopsys\McpAttributes\Attribute\AsMcpColumn;
use Shopsys\McpAttributes\Attribute\AsMcpTable;

#[AsMcpTable]
#[ORM\Table(name: 'closed_days')]
#[ORM\Entity]
class ClosedDay
{
    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Store\Store>
     */
    #[ORM\JoinTable(name: 'closed_day_excluded_stores')]
    #[ORM\ManyToMany(targetEntity: Store::class)]
    protected $excludedStores;

    /**
     * @var int
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'integer')]
    protected $domainId;

    /**
     * @var \DateTimeImmutable
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'date_immutable')]
    protected $date;

    /**
     * @var string
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'string', length: 255)]
    protected $name;

    /**
     * @var bool
     */
    #[AsMcpColumn]
    #[ORM\Column(type: 'boolean')]
    protected $isPublicHoliday;

    public function __construct(ClosedDayData $closedDayData)
    {
        $this->setData($closedDayData);
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Store\Store[]
     */
    public function getExcludedStores()
    {
        return $this->excludedStores->getValues();
    }

    /**
     * @return int
     */
    public function getDomainId()
    {
        return $this->domainId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isPublicHoliday()
    {
        return $this->isPublicHoliday;
    }

    public function edit(ClosedDayData $closedDayData): void
    {
        $this->setData($closedDayData);
    }

    protected function setData(ClosedDayData $closedDayData): void
    {
        $this->excludedStores = new ArrayCollection($closedDayData->excludedStores);
        $this->domainId = $closedDayData->domainId;
        $this->date = $closedDayData->date;
        $this->name = $closedDayData->name;
        $this->isPublicHoliday = $closedDayData->isPublicHoliday;
    }
}
