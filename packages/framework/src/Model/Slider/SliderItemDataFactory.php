<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class SliderItemDataFactory implements SliderItemDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(ImageFacade $imageFacade)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    protected function createInstance(): SliderItemData
    {
        return new SliderItemData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    public function create(): SliderItemData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    public function createFromSliderItem(SliderItem $sliderItem): SliderItemData
    {
        $sliderItemData = $this->createInstance();
        $this->fillFromSliderItem($sliderItemData, $sliderItem);

        return $sliderItemData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $sliderItemData
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem
     */
    protected function fillFromSliderItem(SliderItemData $sliderItemData, SliderItem $sliderItem): void
    {
        $sliderItemData->name = $sliderItem->getName();
        $sliderItemData->link = $sliderItem->getLink();
        $sliderItemData->hidden = $sliderItem->isHidden();
        $sliderItemData->domainId = $sliderItem->getDomainId();
        $sliderItemData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($sliderItem, null);
    }
}
