<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Slider;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class SliderItemDataFactory implements SliderItemDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    protected function createInstance(): SliderItemData
    {
        $sliderItemData = new SliderItemData();
        $sliderItemData->image = $this->imageUploadDataFactory->create();

        return $sliderItemData;
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
        $sliderItemData->image = $this->imageUploadDataFactory->createFromEntityAndType($sliderItem);
    }
}
