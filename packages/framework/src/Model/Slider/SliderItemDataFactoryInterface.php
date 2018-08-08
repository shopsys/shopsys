<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

interface SliderItemDataFactoryInterface
{
    public function create(): SliderItemData;

    public function createFromSliderItem(SliderItem $sliderItem): SliderItemData;
}
