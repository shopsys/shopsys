<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

class AdvertFactory implements AdvertFactoryInterface
{

    public function create(AdvertData $data): Advert
    {
        return new Advert($data);
    }
}
