<?php

declare(strict_types=1);

namespace App\Model\Store\ClosedDay;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="closed_days")
 * @ORM\Entity
 */
class ClosedDay
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \App\Model\Store\Store>
     * @ORM\ManyToMany(targetEntity="\App\Model\Store\Store")
     * @ORM\JoinTable(name="closed_day_excluded_stores")
     */
    private $excludedStores;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $domainId;

    /**
     * @var \DateTimeImmutable
     * @ORM\Column(type="date_immutable")
     */
    private $date;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @param \App\Model\Store\ClosedDay\ClosedDayData $closedDayData
     */
    public function __construct(ClosedDayData $closedDayData)
    {
        $this->setData($closedDayData);
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Model\Store\Store[]
     */
    public function getExcludedStores(): array
    {
        return $this->excludedStores->getValues();
    }

    /**
     * @return int
     */
    public function getDomainId(): int
    {
        return $this->domainId;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getDate(): \DateTimeImmutable
    {
        return $this->date;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param \App\Model\Store\ClosedDay\ClosedDayData $closedDayData
     */
    public function edit(ClosedDayData $closedDayData): void
    {
        $this->setData($closedDayData);
    }

    /**
     * @param \App\Model\Store\ClosedDay\ClosedDayData $closedDayData
     */
    private function setData(ClosedDayData $closedDayData): void
    {
        $this->excludedStores = new ArrayCollection($closedDayData->excludedStores);
        $this->domainId = $closedDayData->domainId;
        $this->date = $closedDayData->date;
        $this->name = $closedDayData->name;
    }
}
