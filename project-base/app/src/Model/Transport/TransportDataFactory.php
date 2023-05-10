<?php

declare(strict_types=1);

namespace App\Model\Transport;

use App\Model\Transport\Type\TransportTypeEnum;
use App\Model\Transport\Type\TransportTypeFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory;
use Shopsys\FrameworkBundle\Component\Image\ImageFacade;
use Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade;
use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;
use Shopsys\FrameworkBundle\Model\Transport\TransportFacade;

/**
 * @method fillFromTransport(\App\Model\Transport\TransportData $transportData, \App\Model\Transport\Transport $transport)
 */
class TransportDataFactory extends BaseTransportDataFactory
{
    /**
     * @param \App\Model\Transport\TransportFacade $transportFacade
     * @param \Shopsys\FrameworkBundle\Model\Pricing\Vat\VatFacade $vatFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \App\Component\Image\ImageFacade $imageFacade
     * @param \Shopsys\FrameworkBundle\Component\FileUpload\ImageUploadDataFactory $imageUploadDataFactory
     * @param \App\Model\Transport\Type\TransportTypeFacade $transportTypeFacade
     */
    public function __construct(
        TransportFacade $transportFacade,
        VatFacade $vatFacade,
        Domain $domain,
        ImageFacade $imageFacade,
        ImageUploadDataFactory $imageUploadDataFactory,
        private readonly TransportTypeFacade $transportTypeFacade
    ) {
        parent::__construct(
            $transportFacade,
            $vatFacade,
            $domain,
            $imageFacade,
            $imageUploadDataFactory
        );
    }

    /**
     * @return \App\Model\Transport\TransportData
     */
    protected function createInstance(): BaseTransportData
    {
        $transportData = new TransportData();
        $transportData->image = $this->imageUploadDataFactory->create();

        return $transportData;
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
    protected function fillNew(BaseTransportData $transportData): void
    {
        parent::fillNew($transportData);

        $transportData->daysUntilDelivery = 0;
        $transportData->transportType = $this->transportTypeFacade->getByCode(TransportTypeEnum::TYPE_COMMON);
        $transportData->trackingUrl = null;

        foreach ($this->domain->getAllLocales() as $locale) {
            $transportData->trackingInstructions[$locale] = null;
        }
    }

    /**
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Transport\TransportData
     */
    public function createFromTransport(BaseTransport $transport): BaseTransportData
    {
        $transportData = $this->createInstance();
        $this->fillFromTransport($transportData, $transport);
        $transportData->personalPickup = $transport->isPersonalPickup();
        $transportData->daysUntilDelivery = $transport->getDaysUntilDelivery();
        $transportData->transportType = $transport->getTransportType();
        $transportData->trackingUrl = $transport->getTrackingUrl();
        $transportData->maxWeight = $transport->getMaxWeight();

        /** @var \App\Model\Transport\TransportTranslation[] $translations */
        $translations = $transport->getTranslations();

        foreach ($translations as $translate) {
            $transportData->trackingInstructions[$translate->getLocale()] = $translate->getTrackingInstruction();
        }

        return $transportData;
    }
}
