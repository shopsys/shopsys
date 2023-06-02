<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Advert;

use App\Model\Advert\AdvertFacade;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class AdvertDataFactory implements AdvertDataFactoryInterface
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
        $advertData = new AdvertData();
        $advertData->image = $this->imageUploadDataFactory->create();
        $advertData->mobileImage = $this->imageUploadDataFactory->create();

        return $advertData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Advert\AdvertData
     */
    public function create(): AdvertData
    {
        return $this->createInstance();
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
        $advertData->image = $this->imageUploadDataFactory->createFromEntityAndType($advert);
        $advertData->categories = $advert->getCategories();

        $advertData->image = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_WEB);
        $advertData->mobileImage = $this->imageUploadDataFactory->createFromEntityAndType($advert, AdvertFacade::IMAGE_TYPE_MOBILE);

        $advertData->datetimeVisibleFrom = $advert->getDatetimeVisibleFrom();
        $advertData->datetimeVisibleTo = $advert->getDatetimeVisibleTo();
        $advertData->categories = $advert->getCategories();
    }
}
