<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;

class StoreDataFactory
{
    public function __construct(
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly OpeningHoursDataFactory $openingHourDataFactory,
    ) {
    }

    public function createForDomain(int $domainId): StoreData
    {
        $storeData = $this->createInstance();
        $storeData->domainId = $domainId;
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();
        $storeData->image = $this->imageUploadDataFactory->create();

        return $storeData;
    }

    public function createFromStore(Store $store): StoreData
    {
        $storeData = $this->createInstance();
        $storeData->domainId = $store->getDomainId();
        $storeData->name = $store->getName();
        $storeData->stock = $store->getStock();
        $storeData->isDefault = $store->isDefault();
        $storeData->description = $store->getDescription();
        $storeData->externalId = $store->getExternalId();
        $storeData->street = $store->getStreet();
        $storeData->city = $store->getCity();
        $storeData->postcode = $store->getPostcode();
        $storeData->country = $store->getCountry();
        $storeData->openingHours = $this->openingHourDataFactory->createWholeWeekOpeningHours($store->getOpeningHours());
        $storeData->phone = $store->getPhone();
        $storeData->email = $store->getEmail();
        $storeData->directions = $store->getDirections();
        $storeData->specialMessage = $store->getSpecialMessage();
        $storeData->latitude = $store->getLatitude();
        $storeData->longitude = $store->getLongitude();
        $storeData->image = $this->imageUploadDataFactory->createFromEntityAndType($store);

        $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
            $store->getDomainId(),
            StoreFriendlyUrlProvider::ROUTE_NAME,
            $store->getId(),
        );
        $storeData->urls->mainFriendlyUrlsByDomainId[$store->getDomainId()] = $mainFriendlyUrl;

        return $storeData;
    }

    protected function createInstance(): StoreData
    {
        return new StoreData();
    }
}
