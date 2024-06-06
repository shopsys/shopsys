<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportType;
use Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade;

class TransportDataFactory implements TransportDataFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \Shopsys\FrameworkBundle\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(
        protected readonly VatFacade $vatFacade,
        protected readonly Domain $domain,
        protected readonly ImageUploadDataFactory $imageUploadDataFactory,
        protected readonly TransportTypeFacade $transportTypeFacade,
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
        $transportData->transportType = $this->transportTypeFacade->getByCode(TransportType::TYPE_COMMON);

        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->enabled[$domainId] = true;
            $transportData->pricesIndexedByDomainId[$domainId] = Money::zero();
            $transportData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
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
            $transportData->pricesIndexedByDomainId[$domainId] = $transport->getPrice($domainId)->getPrice();
            $transportData->vatsIndexedByDomainId[$domainId] = $transport->getTransportDomain($domainId)->getVat();
        }

        $transportData->daysUntilDelivery = $transport->getDaysUntilDelivery();
        $transportData->payments = $transport->getPayments();
        $transportData->image = $this->imageUploadDataFactory->createFromEntityAndType($transport);
        $transportData->transportType = $transport->getTransportType();
        $transportData->maxWeight = $transport->getMaxWeight();
        $transportData->trackingUrl = $transport->getTrackingUrl();
    }
}
