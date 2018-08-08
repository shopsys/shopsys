<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

interface AdvertFactoryInterface
{
    public function create(AdvertData $data): Advert;
}
