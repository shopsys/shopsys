<?php

declare(strict_types=1);

namespace App\Model\Advert;

use Shopsys\FrameworkBundle\Model\Advert\Advert as BaseAdvert;
use Shopsys\FrameworkBundle\Model\Advert\AdvertData as BaseAdvertData;
use Shopsys\FrameworkBundle\Model\Advert\AdvertDataFactory as BaseAdvertDataFactory;

/**
 * @method \App\Model\Advert\AdvertData createFromAdvert(\App\Model\Advert\Advert $advert)
 * @property \App\Component\Image\ImageFacade|null $imageFacade
 * @method __construct(\App\Component\Image\ImageFacade|null $imageFacade)
 * @method setImageFacade(\App\Component\Image\ImageFacade $imageFacade)
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

        $advertData->datetimeVisibleFrom = $advert->getDatetimeVisibleFrom();
        $advertData->datetimeVisibleTo = $advert->getDatetimeVisibleTo();
    }

    /**
     * @return \App\Model\Advert\AdvertData
     */
    public function create(): BaseAdvertData
    {
        return new AdvertData();
    }
}
