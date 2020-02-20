<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Advert\Advert as BaseAdvert;

/**
 * @ORM\Table(name="adverts")
 * @ORM\Entity
 */
class Advert extends BaseAdvert
{
    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleFrom;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(type="datetime",nullable=true)
     */
    protected $datetimeVisibleTo;

    /**
     * @param \App\Model\Advert\AdvertData $advert
     */
    public function __construct($advert)
    {
        parent::__construct($advert);
        $this->datetimeVisibleFrom = $advert->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $advert->datetimeVisibleTo;
    }

    /**
     * @param \App\Model\Advert\AdvertData $advert
     */
    public function edit($advert)
    {
        parent::edit($advert);
        $this->datetimeVisibleFrom = $advert->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $advert->datetimeVisibleTo;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleFrom(): ?\DateTime
    {
        return $this->datetimeVisibleFrom;
    }

    /**
     * @param \DateTime|null $datetimeVisibleFrom
     */
    public function setDatetimeVisibleFrom(?\DateTime $datetimeVisibleFrom): void
    {
        $this->datetimeVisibleFrom = $datetimeVisibleFrom;
    }

    /**
     * @return \DateTime|null
     */
    public function getDatetimeVisibleTo(): ?\DateTime
    {
        return $this->datetimeVisibleTo;
    }

    /**
     * @param \DateTime|null $datetimeVisibleTo
     */
    public function setDatetimeVisibleTo(?\DateTime $datetimeVisibleTo): void
    {
        $this->datetimeVisibleTo = $datetimeVisibleTo;
    }
}
