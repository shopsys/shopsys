<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

interface AdvertDataFactoryInterface
{
    public function create(): AdvertData;

    public function createFromAdvert(Advert $advert): AdvertData;
}
