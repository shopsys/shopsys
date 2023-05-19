<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

interface SliderItemFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $data
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItem
     */
    public function create(SliderItemData $data): SliderItem;
}
