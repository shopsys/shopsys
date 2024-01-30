<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade;
use Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory;

class StoreDataFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Router\FriendlyUrl\FriendlyUrlFacade $friendlyUrlFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursDataFactory $openingHourDataFactory
     */
    public function __construct(
        protected readonly Domain $domain,
        protected readonly FriendlyUrlFacade $friendlyUrlFacade,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly OpeningHoursDataFactory $openingHourDataFactory,
    ) {
    }

    /**
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\StoreData
     */
    public function createForDomain(int $domainId): StoreData
    {
        $storeData = $this->createInstance($domainId);
        $storeData->openingHours = $this->openingHourDataFactory->createWeek();

        return $storeData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\Store $store
     * @return \Shopsys\FrameworkBundle\Model\Store\StoreData
     */
    public function createFromStore(Store $store): StoreData
    {
        $storeData = $this->createInstance($store->getDomainId());
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
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Store\StoreData
     */
    protected function createInstance(int $domainId): StoreData
    {
        $storeData = new StoreData();
        $storeData->image = $this->imageUploadDataFactory->create();
        $storeData->domainId = $domainId;

        return $storeData;
    }
}
