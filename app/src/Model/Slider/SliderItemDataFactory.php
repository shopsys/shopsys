<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory as BaseSliderItemDataFactory;

/**
 * @method \App\Model\Slider\SliderItemData createFromSliderItem(\App\Model\Slider\SliderItem $sliderItem)
 * @method setImageFacade(\App\Component\Image\ImageFacade $imageFacade)
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\App\Component\Image\ImageFacade $imageFacade)
 * @method \App\Model\Slider\SliderItemData create()
 */
class SliderItemDataFactory extends BaseSliderItemDataFactory
{
    /**
     * @return \App\Model\Slider\SliderItemData
     */
    protected function createInstance(): BaseSliderItemData
    {
        $sliderItemData = new SliderItemData();
        $sliderItemData->image = $this->imageUploadDataFactory->create();
        $sliderItemData->mobileImage = $this->imageUploadDataFactory->create();

        return $sliderItemData;
    }

    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @param \App\Model\Slider\SliderItem $sliderItem
     */
    protected function fillFromSliderItem(BaseSliderItemData $sliderItemData, BaseSliderItem $sliderItem): void
    {
        parent::fillFromSliderItem($sliderItemData, $sliderItem);

        $sliderItemData->image = $this->imageUploadDataFactory->createFromEntityAndType($sliderItem, SliderItemFacade::IMAGE_TYPE_WEB);
        $sliderItemData->mobileImage = $this->imageUploadDataFactory->createFromEntityAndType($sliderItem, SliderItemFacade::IMAGE_TYPE_MOBILE);

        $sliderItemData->datetimeVisibleFrom = $sliderItem->getDatetimeVisibleFrom();
        $sliderItemData->datetimeVisibleTo = $sliderItem->getDatetimeVisibleTo();
        $sliderItemData->sliderExtendedText = $sliderItem->getSliderExtendedText();
        $sliderItemData->sliderExtendedTextLink = $sliderItem->getSliderExtendedTextLink();
        $sliderItemData->gtmId = $sliderItem->getGtmId();
        $sliderItemData->gtmCreative = $sliderItem->getGtmCreative();
    }
}
