<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportDataFactory
{
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly TransportInputPricesDataFactory $transportInputPricesDataFactory,
    ) {
    }

    protected function createInstance(): TransportData
    {
        return new TransportData();
    }

    public function create(): TransportData
    {
        $transportData = $this->createInstance();
        $this->fillNew($transportData);

        return $transportData;
    }

    protected function fillNew(TransportData $transportData): void
    {
        $transportData->daysUntilDelivery = 0;
        $transportData->image = $this->imageUploadDataFactory->create();

        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->enabled[$domainId] = false;
            $transportData->inputPricesByDomain[$domainId] = $this->transportInputPricesDataFactory->create($domainId);
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = null;
            $transportData->description[$locale] = null;
            $transportData->instructions[$locale] = null;
            $transportData->trackingInstructions[$locale] = null;
        }
    }

    public function createFromTransport(Transport $transport): TransportData
    {
        $transportData = $this->createInstance();
        $this->fillFromTransport($transportData, $transport);

        return $transportData;
    }

    protected function fillFromTransport(TransportData $transportData, Transport $transport): void
    {
        $names = [];
        $descriptions = [];
        $instructions = [];
        $trackingInstruction = [];

        $translations = $transport->getTranslations();

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
            $descriptions[$translate->getLocale()] = $translate->getDescription();
            $instructions[$translate->getLocale()] = $translate->getInstructions();
            $trackingInstruction[$translate->getLocale()] = $translate->getTrackingInstruction();
        }

        $transportData->name = $names;
        $transportData->description = $descriptions;
        $transportData->instructions = $instructions;
        $transportData->trackingInstructions = $trackingInstruction;
        $transportData->hidden = $transport->isHidden();

        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->enabled[$domainId] = $transport->isEnabled($domainId);
            $transportData->inputPricesByDomain[$domainId] = $this->transportInputPricesDataFactory->createFromTransport($transport, $domainId);
        }

        $transportData->daysUntilDelivery = $transport->getDaysUntilDelivery();
        $transportData->payments = $transport->getPayments();
        $transportData->image = $this->imageUploadDataFactory->createFromEntityAndType($transport);
        $transportData->type = $transport->getType();
        $transportData->trackingUrl = $transport->getTrackingUrl();
    }
}
