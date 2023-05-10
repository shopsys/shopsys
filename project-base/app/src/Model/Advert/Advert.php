<?php

declare(strict_types=1);

namespace App\Model\Advert;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Advert\Advert as BaseAdvert;

/**
 * @ORM\Table(name="adverts")
 * @ORM\Entity
 * @method setData(\App\Model\Advert\AdvertData $advert)
 */
class Advert extends BaseAdvert
{
    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTime|null
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleTo;

    /**
     * @param \App\Model\Advert\AdvertData $advertData
     */
    public function __construct($advertData)
    {
        parent::__construct($advertData);

        $this->datetimeVisibleFrom = $advertData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $advertData->datetimeVisibleTo;
    }

    /**
     * @param \App\Model\Advert\AdvertData $advertData
     */
    public function edit($advertData): void
    {
        parent::edit($advertData);

        $this->datetimeVisibleFrom = $advertData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $advertData->datetimeVisibleTo;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleFrom(): ?DateTime
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @param \DateTime|null $datetimeVisibleFrom
     */
    public function setDatetimeVisibleFrom(?DateTime $datetimeVisibleFrom): void
    {
        $this->datetimeVisibleFrom = $datetimeVisibleFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleTo(): ?DateTime
    {
        return $this->datetimeVisibleTo;
    }

    /**
     * @param \DateTime|null $datetimeVisibleTo
     */
    public function setDatetimeVisibleTo(?DateTime $datetimeVisibleTo): void
    {
        $this->datetimeVisibleTo = $datetimeVisibleTo;
    }
}
