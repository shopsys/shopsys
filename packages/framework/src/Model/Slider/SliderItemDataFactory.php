<?php

namespace Shopsys\FrameworkBundle\Model\Slider;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class SliderItemDataFactory implements SliderItemDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null $imageFacade
     */
    public function __construct(?ImageFacade $imageFacade = null)
    {
        $this->imageFacade = $imageFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function setImageFacade(ImageFacade $imageFacade): void
    {
        if ($this->imageFacade !== null && $this->imageFacade !== $imageFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageFacade = $imageFacade;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    public function create(): SliderItemData
    {
        return new SliderItemData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem
     * @return \Shopsys\FrameworkBundle\Model\Slider\SliderItemData
     */
    public function createFromSliderItem(SliderItem $sliderItem): SliderItemData
    {
        $sliderItemData = new SliderItemData();
        $this->fillFromSliderItem($sliderItemData, $sliderItem);

        return $sliderItemData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItemData $sliderItemData
     * @param \Shopsys\FrameworkBundle\Model\Slider\SliderItem $sliderItem
     */
    protected function fillFromSliderItem(SliderItemData $sliderItemData, SliderItem $sliderItem)
    {
        $sliderItemData->name = $sliderItem->getName();
        $sliderItemData->link = $sliderItem->getLink();
        $sliderItemData->hidden = $sliderItem->isHidden();
        $sliderItemData->domainId = $sliderItem->getDomainId();
        $sliderItemData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($sliderItem, null);
    }
}
