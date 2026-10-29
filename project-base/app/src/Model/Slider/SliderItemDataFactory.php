<?php

declare(strict_types=1);

namespace App\Model\Slider;

use Override;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemData as BaseSliderItemData;
use Shopsys\FrameworkBundle\Model\Slider\SliderItemDataFactory as BaseSliderItemDataFactory;

/**
 * @method \App\Model\Slider\SliderItemData create()
 * @method \App\Model\Slider\SliderItemData createFromSliderItem(\App\Model\Slider\SliderItem $sliderItem)
 * @method void fillFromSliderItem(\App\Model\Slider\SliderItemData $sliderItemData, \App\Model\Slider\SliderItem $sliderItem)
 * @method __construct(\Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory)
 */
class SliderItemDataFactory extends BaseSliderItemDataFactory
{
    /**
     * @return \App\Model\Slider\SliderItemData
     */
    #[Override]
    protected function createInstance(): BaseSliderItemData
    {
        return new SliderItemData();
    }
}
