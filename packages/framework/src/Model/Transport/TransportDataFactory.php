<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;

class TransportDataFactory implements TransportDataFactoryInterface
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\TransportFacade
     */
    protected $transportFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade
     */
    protected $vatFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    protected $domain;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade
     */
    protected $imageFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->vatFacade = $vatFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    protected function createInstance(): TransportData
    {
        return new TransportData();
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
    protected function fillNew(TransportData $transportData)
    {
        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->enabled[$domainId] = true;
            $transportData->pricesIndexedByDomainId[$domainId] = Money::zero();
            $transportData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatForDomain($domainId);
        }

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->name[$locale] = null;
            $transportData->description[$locale] = null;
            $transportData->instructions[$locale] = null;
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
    protected function fillFromTransport(TransportData $transportData, Transport $transport)
    {
        $names = [];
        $descriptions = [];
        $instructions = [];

        /** @var \Shopsys\FrameworkBundle\Model\Transport\TransportTranslation[] $translations */
        $translations = $transport->getTranslations();

        foreach ($translations as $translate) {
            $names[$translate->getLocale()] = $translate->getName();
            $descriptions[$translate->getLocale()] = $translate->getDescription();
            $instructions[$translate->getLocale()] = $translate->getInstructions();
        }

        $transportData->name = $names;
        $transportData->description = $descriptions;
        $transportData->instructions = $instructions;
        $transportData->hidden = $transport->isHidden();

        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->enabled[$domainId] = $transport->isEnabled($domainId);
            $transportData->pricesIndexedByDomainId[$domainId] = $transport->getPrice($domainId)->getPrice();
            $transportData->vatsIndexedByDomainId[$domainId] = $transport->getTransportDomain($domainId)->getVat();
        }

        $transportData->payments = $transport->getPayments();
        $transportData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($transport, null);
    }
}
