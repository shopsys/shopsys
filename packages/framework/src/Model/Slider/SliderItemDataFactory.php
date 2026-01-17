<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class SliderItemDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): SliderItemData
    {
        return new SliderItemData();
    }

    public function create(): SliderItemData
    {
        $sliderItemData = $this->createInstance();

        $sliderItemData->image = $this->imageUploadDataFactory->create();

        return $sliderItemData;
    }

    public function createFromSliderItem(SliderItem $sliderItem): SliderItemData
    {
        $sliderItemData = $this->createInstance();
        $this->fillFromSliderItem($sliderItemData, $sliderItem);

        return $sliderItemData;
    }

    protected function fillFromSliderItem(SliderItemData $sliderItemData, SliderItem $sliderItem): void
    {
        $sliderItemData->name = $sliderItem->getName();
        $sliderItemData->link = $sliderItem->getLink();
        $sliderItemData->hidden = $sliderItem->isHidden();
        $sliderItemData->domainId = $sliderItem->getDomainId();
        $sliderItemData->image = $this->imageUploadDataFactory->createFromEntityAndType($sliderItem);
        $sliderItemData->description = $sliderItem->getDescription();
        $sliderItemData->rgbBackgroundColor = $sliderItem->getRgbBackgroundColor();
        $sliderItemData->opacity = $sliderItem->getOpacity();
        $sliderItemData->datetimeVisibleFrom = $sliderItem->getDatetimeVisibleFrom();
        $sliderItemData->datetimeVisibleTo = $sliderItem->getDatetimeVisibleTo();
    }
}
