<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

class SliderItemFactory implements SliderItemFactoryInterface
{
    public function create(SliderItemData $data): SliderItem
    {
        return new SliderItem($data);
    }
}
