<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

interface SliderItemFactoryInterface
{
    public function create(SliderItemData $data): SliderItem;
}
