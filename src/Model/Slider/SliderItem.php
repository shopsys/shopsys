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
     * @var string|null
     *
     * @ORM\Column(type="text",nullable=true)
     */
    protected $sliderExtendedText;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text",nullable=true)
     */
    protected $sliderExtendedTextLink;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text",nullable=false)
     */
    protected $gtmId;

    /**
     * @var string|null
     *
     * @ORM\Column(type="text",nullable=true)
     */
    protected $gtmCreative;

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function __construct($sliderItemData)
    {
        parent::__construct($sliderItemData);
        $this->datetimeVisibleFrom = $sliderItemData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $sliderItemData->datetimeVisibleTo;
        $this->sliderExtendedText = $sliderItemData->sliderExtendedText;
        $this->sliderExtendedTextLink = $sliderItemData->sliderExtendedTextLink;
        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function edit($sliderItemData)
    {
        parent::edit($sliderItemData);
        $this->datetimeVisibleFrom = $sliderItemData->datetimeVisibleFrom;
        $this->datetimeVisibleTo = $sliderItemData->datetimeVisibleTo;
        $this->sliderExtendedText = $sliderItemData->sliderExtendedText;
        $this->sliderExtendedTextLink = $sliderItemData->sliderExtendedTextLink;
        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
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

    /**
     * @return string|null
     */
    public function getSliderExtendedText(): ?string
    {
        return $this->sliderExtendedText;
    }

    /**
     * @return string|null
     */
    public function getSliderExtendedTextLink(): ?string
    {
        return $this->sliderExtendedTextLink;
    }

    /**
     * @return string
     */
    public function getGtmId(): string
    {
        return $this->gtmId;
    }

    /**
     * @param string|null $gtmId
     */
    public function setGtmId($gtmId): void
    {
        $this->gtmId = $gtmId;
    }

    /**
     * @return  string|null
     */
    public function getGtmCreative(): ?string
    {
        return $this->gtmCreative;
    }

    /**
     * @param string|null $gtmCreative
     */
    public function setGtmCreative($gtmCreative): void
    {
        $this->gtmCreative = $gtmCreative;
    }
}
