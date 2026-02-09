<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Store\Store;

#[ORM\Table(name: 'store_opening_hours')]
#[ORM\Entity]
class OpeningHours
{
    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    protected $id;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Store\Store
     */
    #[ORM\JoinColumn(nullable: false)]
    #[ORM\ManyToOne(targetEntity: Store::class, inversedBy: 'openingHours')]
    protected $store;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer')]
    protected $dayOfWeek;

    /**
     * @var \Doctrine\Common\Collections\Collection<int, \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange>
     */
    #[ORM\OneToMany(targetEntity: OpeningHoursRange::class, mappedBy: 'openingHours', cascade: ['persist'], orphanRemoval: true)]
    protected $openingHoursRanges;

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
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange[]
     */
    public function getOpeningHoursRanges()
    {
        return $this->openingHoursRanges->getValues();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursRange[] $openingHoursRanges
     */
    public function setOpeningHoursRanges($openingHoursRanges): void
    {
        $this->openingHoursRanges = new ArrayCollection($openingHoursRanges);
    }

    protected function setData(OpeningHoursData $openingHourData): void
    {
        $this->dayOfWeek = $openingHourData->dayOfWeek;
    }
}
