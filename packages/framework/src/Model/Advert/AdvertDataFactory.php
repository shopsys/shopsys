<?php

namespace Shopsys\FrameworkBundle\Model\Advert;

class AdvertDataFactory implements AdvertDataFactoryInterface
{
    public function create(): AdvertData
    {
        return new AdvertData();
    }

    public function createFromAdvert(Advert $advert): AdvertData
    {
        $advertData = new AdvertData();
        $this->fillFromAdvert($advertData, $advert);
        return $advertData;
    }

    protected function fillFromAdvert(AdvertData $advertData, Advert $advert)
    {
        $advertData->name = $advert->getName();
        $advertData->type = $advert->getType();
        $advertData->code = $advert->getCode();
        $advertData->link = $advert->getLink();
        $advertData->positionName = $advert->getPositionName();
        $advertData->hidden = $advert->isHidden();
        $advertData->domainId = $advert->getDomainId();
    }
}
