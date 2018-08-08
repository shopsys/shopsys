<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

interface AdvertDataFactoryInterface
{
    public function create(): AdvertData;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Advert\Advert
     */
    public function createFromAdvert(Advert $advert): AdvertData;
}
