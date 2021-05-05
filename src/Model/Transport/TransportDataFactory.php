<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\Transport as BaseTransport;
use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;

class TransportDataFactory extends BaseTransportDataFactory
{
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

        $transportData->daysUntilDelivery = 0;
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

        $transportData->isOverLimitTransport = $transport->isOverLimitTransport();
        $transportData->daysUntilDelivery = $transport->getDaysUntilDelivery();

        $transportData->deliveryCode = $transport->getDeliveryCode();
        $transportData->typeOfDeliveryKey = $transport->getTypeOfDeliveryKey();

        $transportData->trackingUrl = $transport->getTrackingUrl();

        /** @var \App\Model\Transport\TransportTranslation[] $translations */
        $translations = $transport->getTranslations();

        foreach ($translations as $translate) {
            $transportData->trackingInstructions[$translate->getLocale()] = $translate->getTrackingInstruction();
        }

        return $transportData;
    }
}
