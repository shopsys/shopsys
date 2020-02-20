<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;

/**
 * SliderItem
 *
 * @ORM\Table(name="slider_items")
 * @ORM\Entity
 */
class SliderItem extends BaseSliderItem
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
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function __construct($sliderItemData)
    {
        parent::__construct($sliderItemData);
        $this->datetimeVisibleFrom = $sliderItemData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $sliderItemData->datetimeVisibleTo;
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function edit($sliderItemData)
    {
        parent::edit($sliderItemData);
        $this->datetimeVisibleFrom = $sliderItemData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $sliderItemData->datetimeVisibleTo;
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
