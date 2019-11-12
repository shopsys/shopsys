<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use BadMethodCallException;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade;
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
     * @var \Shopsys\FrameworkBundle\Component\Image\ImageFacade|null
     */
    protected $imageFacade;

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade
     */
    protected $currencyFacade;

    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Currency\CurrencyFacade $currencyFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade,
        CurrencyFacade $currencyFacade
    ) {
        $this->transportFacade = $transportFacade;
        $this->vatFacade = $vatFacade;
        $this->domain = $domain;
        $this->imageFacade = $imageFacade;
        $this->currencyFacade = $currencyFacade;
    }

    /**
     * @required
     * @internal This function will be replaced by constructor injection in next major
     * @param \Shopsys\FrameworkBundle\Component\Image\ImageFacade $imageFacade
     */
    public function setImageFacade(ImageFacade $imageFacade): void
    {
        if ($this->imageFacade !== null && $this->imageFacade !== $imageFacade) {
            throw new BadMethodCallException(sprintf('Method "%s" has been already called and cannot be called multiple times.', __METHOD__));
        }
        if ($this->imageFacade === null) {
            @trigger_error(sprintf('The %s() method is deprecated and will be removed in the next major. Use the constructor injection instead.', __METHOD__), E_USER_DEPRECATED);
            $this->imageFacade = $imageFacade;
        }
    }

    /**
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportData
     */
    public function create(): TransportData
    {
        $transportData = new TransportData();
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
            $transportData->vatsIndexedByDomainId[$domainId] = $this->vatFacade->getDefaultVatFormDomain($domainId);
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
        $transportData = new TransportData();
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
            $defaultCurrencyForDomain = $this->currencyFacade->getDomainDefaultCurrencyByDomainId($domainId);

            $transportData->enabled[$domainId] = $transport->isEnabled($domainId);
            $transportData->pricesIndexedByDomainId[$domainId] = $transport->getPriceByCurrencyAndDomainId($defaultCurrencyForDomain, $domainId);
            $transportData->vatsIndexedByDomainId[$domainId] = $transport->getVatByCurrencyAndDomainId($defaultCurrencyForDomain, $domainId);
        }

        $transportData->payments = $transport->getPayments();
        $transportData->image->orderedImages = $this->imageFacade->getImagesByEntityIndexedById($transport, null);
    }
}
