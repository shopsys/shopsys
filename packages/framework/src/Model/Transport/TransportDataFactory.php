<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportDataFactory implements TransportDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportInputPricesDataFactory $transportInputPricesDataFactory
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly TransportInputPricesDataFactory $transportInputPricesDataFactory,
    ) {
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    protected function createInstance(): TransportData
    {
        $transportData = new TransportData();
        $transportData->image = $this->imageUploadDataFactory->create();

        return $transportData;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    public function create(): TransportData
    {
        $transportData = $this->createInstance();
        $this->fillNew($transportData);

        return $transportData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     */
    protected function fillNew(TransportData $transportData): void
    {
        $transportData->daysUntilDelivery = 0;

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

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    public function createFromTransport(Transport $transport): TransportData
    {
        $transportData = $this->createInstance();
        $this->fillFromTransport($transportData, $transport);

        return $transportData;
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportData $transportData
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     */
    protected function fillFromTransport(TransportData $transportData, Transport $transport): void
    {
        $names = [];
        $descriptions = [];
        $instructions = [];
        $trackingInstruction = [];

        /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation[] $translations */
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
