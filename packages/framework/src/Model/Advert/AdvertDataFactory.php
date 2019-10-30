<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;

class AdvertDataFactory implements AdvertDataFactoryInterface
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
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    public function create(): AdvertData
    {
        return new AdvertData();
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    public function createFromAdvert(Advert $advert): AdvertData
    {
        $advertData = new AdvertData();
        $this->fillFromAdvert($advertData, $advert);
        return $advertData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\AdvertData $advertData
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert $advert
     */
    protected function fillFromAdvert(AdvertData $advertData, Advert $advert)
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
