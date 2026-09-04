<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\AdditionalService;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;

class AdditionalServiceDataFactory
{
    public function __construct(
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
    ) {
    }

    protected function createInstance(): AdditionalServiceData
    {
        return new AdditionalServiceData();
    }

    public function create(): AdditionalServiceData
    {
        $additionalServiceData = $this->createInstance();
        $this->fillNew($additionalServiceData);

        return $additionalServiceData;
    }

    protected function fillNew(AdditionalServiceData $additionalServiceData): void
    {
        $additionalServiceData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $additionalServiceData->enabledByDomainId[$domainId] = true;
            $additionalServiceData->showInFeedsByDomainId[$domainId] = true;
            $additionalServiceData->useProductVatRateByDomainId[$domainId] = true;
            $additionalServiceData->vatsIndexedByDomainId[$domainId] = null;
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = Money::zero();
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $additionalServiceData->name[$locale] = null;
            $additionalServiceData->feedName[$locale] = null;
            $additionalServiceData->zboziDescription[$locale] = null;
            $additionalServiceData->description[$locale] = null;
        }
    }

    public function createFromAdditionalService(AdditionalService $additionalService): AdditionalServiceData
    {
        $additionalServiceData = $this->createInstance();
        $this->fillFromAdditionalService($additionalServiceData, $additionalService);

        return $additionalServiceData;
    }

    protected function fillFromAdditionalService(
        AdditionalServiceData $additionalServiceData,
        AdditionalService $additionalService,
    ): void {
        $additionalServiceData->catnum = $additionalService->getCatnum();
        $additionalServiceData->zboziServiceType = $additionalService->getZboziServiceType();
        $additionalServiceData->deliveryDaysExtension = $additionalService->getDeliveryDaysExtension();
        $additionalServiceData->uuid = $additionalService->getUuid();

        foreach ($additionalService->getTranslations() as $translation) {
            $locale = $translation->getLocale();
            $additionalServiceData->name[$locale] = $translation->getName();
            $additionalServiceData->feedName[$locale] = $translation->getFeedName();
            $additionalServiceData->zboziDescription[$locale] = $translation->getZboziDescription();
            $additionalServiceData->description[$locale] = $translation->getDescription();
        }

        foreach ($this->domain->getAllIds() as $domainId) {
            $additionalServiceDomain = $additionalService->getAdditionalServiceDomain($domainId);
            $additionalServiceData->enabledByDomainId[$domainId] = $additionalServiceDomain->isEnabled();
            $additionalServiceData->showInFeedsByDomainId[$domainId] = $additionalServiceDomain->isShownInFeeds();
            $additionalServiceData->useProductVatRateByDomainId[$domainId] = $additionalServiceDomain->isProductVatRateUsed();
            $additionalServiceData->vatsIndexedByDomainId[$domainId] = $additionalServiceDomain->getVat();
            $additionalServiceData->pricesIndexedByDomainId[$domainId] = $additionalServiceDomain->getPrice();
        }

        $additionalServiceData->image = $this->imageUploadDataFactory->createFromEntityAndType($additionalService);
    }
}
