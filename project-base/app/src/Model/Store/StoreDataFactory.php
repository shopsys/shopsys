<?php

declare(strict_types=1);

namespace App\Model\Store;

use App\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use App\Model\Store\OpeningHours\OpeningHoursDataFactory;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;

class StoreDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \App\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHourDataFactory
     */
    public function __construct(
        private readonly Domain $domain,
        private readonly FriendlyUrlFacade $friendlyUrlFacade,
        private readonly ImageUploadDataFactory $imageUploadDataFactory,
        private readonly OpeningHoursDataFactory $openingHourDataFactory,
    ) {
    }

    /**
     * @return \App\Model\Store\StoreData
     */
    public function create(): StoreData
    {
        $storeData = $this->createInstance();
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();

        return $storeData;
    }

    /**
     * @param \App\Model\Store\Store $store
     * @return \App\Model\Store\StoreData
     */
    public function createFromStore(Store $store): StoreData
    {
        $storeData = $this->createInstance();
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
        $storeData->contactInfo = $store->getContactInfo();
        $storeData->specialMessage = $store->getSpecialMessage();
        $storeData->locationLatitude = $store->getLocationLatitude();
        $storeData->locationLongitude = $store->getLocationLongitude();
        $storeData->image = $this->imageUploadDataFactory->createFromEntityAndType($store);

        foreach ($this->domain->getAllIds() as $domainId) {
            $storeData->isEnabledOnDomains[$domainId] = $store->isEnabled($domainId);

            $mainFriendlyUrl = $this->friendlyUrlFacade->findMainFriendlyUrl(
                $domainId,
                StoreFriendlyUrlProvider::ROUTE_NAME,
                $store->getId(),
            );
            $storeData->urls->mainFriendlyUrlsByDomainId[$domainId] = $mainFriendlyUrl;
        }

        return $storeData;
    }

    /**
     * @return \App\Model\Store\StoreData
     */
    private function createInstance(): StoreData
    {
        $storeData = new StoreData();
        $storeData->image = $this->imageUploadDataFactory->create();

        return $storeData;
    }
}
