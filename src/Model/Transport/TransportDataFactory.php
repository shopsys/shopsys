<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Component\Pricing\PriceToAndPriceData;
use App\Model\Transport\TransportPackage\TransportPackageDataFactory;
use App\Model\Transport\TransportPackage\TransportPackageRepository;
use App\Model\Transport\TransportPallet\TransportPalletPriceFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

class TransportDataFactory extends BaseTransportDataFactory
{
    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageRepository
     */
    private TransportPackageRepository $transportPackageRepository;

    /**
     * @var \App\Model\Transport\TransportPackage\TransportPackageDataFactory
     */
    private TransportPackageDataFactory $transportPackageDataFactory;

    /**
     * @var \App\Model\Transport\TransportPallet\TransportPalletPriceFacade
     */
    private TransportPalletPriceFacade $transportPalletPriceFacade;

    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \App\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \App\Model\Transport\TransportPackage\TransportPackageRepository $transportPackageRepository
     * @param \App\Model\Transport\TransportPackage\TransportPackageDataFactory $transportPackageDataFactory
     * @param \App\Model\Transport\TransportPallet\TransportPalletPriceFacade $transportPalletPriceFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade,
        TransportPackageRepository $transportPackageRepository,
        TransportPackageDataFactory $transportPackageDataFactory,
        TransportPalletPriceFacade $transportPalletPriceFacade
    ) {
        parent::__construct($transportFacade, $vatFacade, $domain, $imageFacade);
        $this->transportPackageRepository = $transportPackageRepository;
        $this->transportPackageDataFactory = $transportPackageDataFactory;
        $this->transportPalletPriceFacade = $transportPalletPriceFacade;
    }

    /**
     * @return \App\Model\Transport\TransportData
     */
    protected function createInstance(): BaseTransportData
    {
        return new TransportData();
    }

    /**
     * @return \App\Model\Transport\TransportData
     */
    public function create(): BaseTransportData
    {
        $transportData = $this->createInstance();
        $this->fillNew($transportData);

        return $transportData;
    }

    /**
     * @param \App\Model\Transport\TransportData $transportData
     */
    protected function fillNew(BaseTransportData $transportData)
    {
        parent::fillNew($transportData);
        $transportData->type = Transport::TYPE_COMMON;
        $transportData->daysUntilDelivery = 0;
        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->palletPricesByDomainId[$domainId] = [];
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Transport\TransportData $transportData
     */
    public function createFromTransport(BaseTransport $transport): BaseTransportData
    {
        $transportData = $this->createInstance();
        $this->fillFromTransport($transportData, $transport);
        $transportData->productTypes = $transport->getProductTypes();
        $transportData->personalPickup = $transport->isPersonalPickup();
        $transportData->type = $transport->getType();

        $transportData->transportPackages = [];
        $transportPackages = $this->transportPackageRepository->getTransportPackagesByTransport($transport);
        foreach ($transportPackages as $transportPackage) {
            $transportData->transportPackages[] = $this->transportPackageDataFactory->createFromTransportPackage($transportPackage);
        }
        $transportData->isOverLimitTransport = $transport->isOverLimitTransport();
        $transportData->daysUntilDelivery = $transport->getDaysUntilDelivery();

        $transportData->externalId = $transport->getExternalId();
        $transportData->deliveryCode = $transport->getDeliveryCode();
        $transportData->typeOfDeliveryKey = $transport->getTypeOfDeliveryKey();

        foreach ($this->domain->getAllIds() as $domainId) {
            $transportData->palletPricesByDomainId[$domainId] = [];
            $transportPalletPrices = $this->transportPalletPriceFacade->getSortedPalletPricesByTransportAndDomain($transport, $domainId);
            foreach ($transportPalletPrices as $transportPalletPrice) {
                $priceToAndPriceData = new PriceToAndPriceData();
                $priceToAndPriceData->priceTo = $transportPalletPrice->getProductsPriceTo();
                $priceToAndPriceData->price = $transportPalletPrice->getTransportPrice();
                $transportData->palletPricesByDomainId[$domainId][] = $priceToAndPriceData;
            }
        }

        return $transportData;
    }
}
