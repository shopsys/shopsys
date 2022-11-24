<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class AdvertDataFactory implements AdvertDataFactoryInterface
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
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    protected function createInstance(): AdvertData
    {
        return new AdvertData();
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    public function create(): AdvertData
    {
        return $this->createInstance();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    public function createFromAdvert(Advert $advert): AdvertData
    {
        $advertData = $this->createInstance();
        $this->fillFromAdvert($advertData, $advert);
        return $advertData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     */
    protected function fillFromAdvert(AdvertData $advertData, Advert $advert): void
    {
        $advertData->name = $advert->getName();
        $advertData->type = $advert->getType();
        $advertData->code = $advert->getCode();
        $advertData->link = $advert->getLink();
        $advertData->positionName = $advert->getPositionName();
        $advertData->hidden = $advert->isHidden();
        $advertData->domainId = $advert->getDomainId();
        $advertData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($advert, null);
    }
}
