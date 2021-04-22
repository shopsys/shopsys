<?php

declare(strict_types=1);

namespace App\Model\Store;

use Shopsys\FrameworkBundle\Component\Domain\Domain;

class StoreDataFactory
{
    private Domain $domain;

    /**
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     */
    public function __construct(Domain $domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return \App\Model\Store\StoreData
     */
    public function create(): StoreData
    {
        return $this->createInstance();
    }

    /**
     * @param \App\Model\Store\Store $store
     * @return \App\Model\Store\StoreData
     */
    public function createFromStore(Store $store): StoreData
    {
        $storeData = $this->createInstance();
        $storeData->name = $store->getName();
        $storeData->isDefault = $store->isDefault();
        $storeData->description = $store->getDescription();
        $storeData->externalId = $store->getExternalId();
        $storeData->address = $store->getAddress();
        $storeData->openingHours = $store->getOpeningHours();
        $storeData->contactInfo = $store->getContactInfo();
        $storeData->specialMessage = $store->getSpecialMessage();
        $storeData->locationLatitude = $store->getLocationLatitude();
        $storeData->locationLongitude = $store->getLocationLongitude();

        foreach ($this->domain->getAllIds() as $domainId) {
            $storeData->isEnabledOnDomains[$domainId] = $store->isEnabled($domainId);
        }

        return $storeData;
    }

    /**
     * @return \App\Model\Store\StoreData
     */
    private function createInstance(): StoreData
    {
        return new StoreData();
    }
}
