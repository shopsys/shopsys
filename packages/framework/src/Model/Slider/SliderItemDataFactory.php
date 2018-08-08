<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

class SliderItemDataFactory implements SliderItemDataFactoryInterface
{
    public function create(): SliderItemData
    {
        return new SliderItemData();
    }

    public function createFromSliderItem(SliderItem $sliderItem): SliderItemData
    {
        $sliderItemData = new SliderItemData();
        $this->fillFromSliderItem($sliderItemData, $sliderItem);

        return $sliderItemData;
    }

    protected function fillFromSliderItem(SliderItemData $sliderItemData, SliderItem $sliderItem): void
    {
        $sliderItemData->name = $sliderItem->getName();
        $sliderItemData->link = $sliderItem->getLink();
        $sliderItemData->hidden = $sliderItem->isHidden();
        $sliderItemData->domainId = $sliderItem->getDomainId();
    }
}
