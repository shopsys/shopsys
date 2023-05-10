<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\Advert as BaseAdvert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData as BaseAdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory as BaseAdvertDataFactory;

/**
 * @property \App\Component\Image\ImageFacade $imageFacade
 * @method __construct(\App\Component\Image\ImageFacade $imageFacade)
 * @method \App\Model\Advert\AdvertData create()
 * @method \App\Model\Advert\AdvertData createFromAdvert(\App\Model\Advert\Advert $advert)
 */
class AdvertDataFactory extends BaseAdvertDataFactory
{
    /**
     * @param \App\Model\Advert\AdvertData $advertData
     * @param \App\Model\Advert\Advert $advert
     */
    protected function fillFromAdvert(BaseAdvertData $advertData, BaseAdvert $advert): void
    {
        parent::fillFromAdvert($advertData, $advert);

        $advertData->image = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_WEB);
        $advertData->mobileImage = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_MOBILE);

        $advertData->datetimeVisibleFrom = $advert->getDatetimeVisibleFrom();
        $advertData->datetimeVisibleTo = $advert->getDatetimeVisibleTo();
        $advertData->categories = $advert->getCategories();
    }

    /**
     * @return \App\Model\Advert\AdvertData
     */
    public function createInstance(): BaseAdvertData
    {
        $advertData = new AdvertData();
        $advertData->image = $this->imageUploadDataFactory->create();
        $advertData->mobileImage = $this->imageUploadDataFactory->create();

        return $advertData;
    }
}
