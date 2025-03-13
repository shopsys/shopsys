<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Doctrine\ORM\Mapping as ORM;
use Override;
use Ramsey\Uuid\Uuid;
use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;

/**
 * SliderItem
 *
 * @ORM\Table(name="slider_items")
 * @ORM\Entity
 * @method setData(\App\Model\Slider\SliderItemData $sliderItemData)
 */
class SliderItem extends BaseSliderItem
{
    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=false)
     */
    protected $gtmId;

    /**
     * @var string|null
     * @ORM\Column(type="text",nullable=true)
     */
    protected $gtmCreative;

    /**
     * @var string
     * @ORM\Column(type="guid", unique=true)
     */
    protected string $uuid;

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    public function __construct($sliderItemData)
    {
        parent::__construct($sliderItemData);

        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
        $this->uuid = $sliderItemData->uuid ?: Uuid::uuid4()->toString();
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     */
    #[Override]
    public function edit($sliderItemData)
    {
        parent::edit($sliderItemData);

        $this->gtmId = $sliderItemData->gtmId;
        $this->gtmCreative = $sliderItemData->gtmCreative;
    }

    /**
     * @return string
     */
    public function getGtmId(): string
    {
        return $this->gtmId;
    }

    /**
     * @return  string|null
     */
    public function getGtmCreative(): ?string
    {
        return $this->gtmCreative;
    }

    /**
     * @return string
     */
    public function getUuid(): string
    {
        return $this->uuid;
    }
}
