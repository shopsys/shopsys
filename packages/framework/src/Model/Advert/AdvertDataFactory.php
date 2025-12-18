<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class AdvertDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     */
    public function __construct(
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
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
        $advertData = $this->createInstance();

        $advertData->image = $this->imageUploadDataFactory->create();
        $advertData->mobileImage = $this->imageUploadDataFactory->create();

        return $advertData;
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
        $advertData->categories = $advert->getCategories();

        $advertData->image = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_WEB);
        $advertData->mobileImage = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_MOBILE);

        $advertData->datetimeVisibleFrom = $advert->getDatetimeVisibleFrom();
        $advertData->datetimeVisibleTo = $advert->getDatetimeVisibleTo();
        $advertData->categories = $advert->getCategories();
    }
}
