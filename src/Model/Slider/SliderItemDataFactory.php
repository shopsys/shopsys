<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Shopsys\FrameworkBundle\Model\Slider\SliderItem as BaseSliderItem;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory as BaseSliderItemDataFactory;

/**
 * @method \App\Model\Slider\SliderItemData createFromSliderItem(\App\Model\Slider\SliderItem $sliderItem)
 * @property \App\Component\Image\ImageFacade|null $imageFacade
 * @method __construct(\App\Component\Image\ImageFacade|null $imageFacade)
 * @method setImageFacade(\App\Component\Image\ImageFacade $imageFacade)
 */
class SliderItemDataFactory extends BaseSliderItemDataFactory
{
    /**
     * @param \App\Model\Slider\SliderItemData $sliderItemData
     * @param \App\Model\Slider\SliderItem $sliderItem
     */
    protected function fillFromSliderItem(BaseSliderItemData $sliderItemData, BaseSliderItem $sliderItem): void
    {
        parent::fillFromSliderItem($sliderItemData, $sliderItem);

        $sliderItemData->datetimeVisibleFrom = $sliderItem->getDatetimeVisibleFrom();
        $sliderItemData->datetimeVisibleTo = $sliderItem->getDatetimeVisibleTo();
        $sliderItemData->sliderExtendedText = $sliderItem->getSliderExtendedText();
        $sliderItemData->sliderExtendedTextLink = $sliderItem->getSliderExtendedTextLink();
    }

    /**
     * @return \App\Model\Slider\SliderItemData
     */
    public function create(): BaseSliderItemData
    {
        return new SliderItemData();
    }
}
