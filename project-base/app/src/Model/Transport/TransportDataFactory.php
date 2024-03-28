<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;

/**
 * @method fillFromTransport(\App\Model\Transport\TransportData $transportData, \App\Model\Transport\Transport $transport)
 */
class TransportDataFactory extends BaseTransportDataFactory
{
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
