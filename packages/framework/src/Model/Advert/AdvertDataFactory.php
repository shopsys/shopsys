<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class AdvertDataFactory
{
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): AdvertData
    {
        return new AdvertData();
    }

    public function create(): AdvertData
    {
        $advertData = $this->createInstance();

        $advertData->image = $this->imageUploadDataFactory->create();
        $advertData->mobileImage = $this->imageUploadDataFactory->create();

        return $advertData;
    }

    public function createFromAdvert(Advert $advert): AdvertData
    {
        $advertData = $this->createInstance();
        $this->fillFromAdvert($advertData, $advert);

        return $advertData;
    }

    protected function fillFromAdvert(AdvertData $advertData, Advert $advert): void
    {
        $advertData->name = $advert->getName();
        $advertData->type = $advert->getType();
        $advertData->code = $advert->getCode();
        $advertData->link = $advert->getLink();
        $advertData->positionName = $advert->getPositionName();
        $advertData->hidden = $advert->isHidden();
        $advertData->domainId = $advert->getDomainId();
        $advertData->categories = $advert->getCategories();

        $advertData->image = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_WEB);
        $advertData->mobileImage = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_MOBILE);

        $advertData->datetimeVisibleFrom = $advert->getDatetimeVisibleFrom();
        $advertData->datetimeVisibleTo = $advert->getDatetimeVisibleTo();
        $advertData->categories = $advert->getCategories();
    }
}
