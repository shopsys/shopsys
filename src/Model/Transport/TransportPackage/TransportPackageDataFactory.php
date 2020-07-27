<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

class TransportPackageDataFactory
{
    /**
     * @param \App\Model\Transport\TransportPackage\TransportPackage $transportPackage
     * @return \App\Model\Transport\TransportPackage\TransportPackageData
     */
    public function createFromTransportPackage(TransportPackage $transportPackage): TransportPackageData
    {
        $transportPackageData = new TransportPackageData();
        $this->fillFromTransportPackage($transportPackageData, $transportPackage);

        return $transportPackageData;
    }

    /**
     * @return \App\Model\Transport\TransportPackage\TransportPackageData
     */
    public function create(): TransportPackageData
    {
        return new TransportPackageData();
    }

    /**
     * @param \App\Model\Transport\TransportPackage\TransportPackageData $transportPackageData
     * @param \App\Model\Transport\TransportPackage\TransportPackage $transportPackage
     */
    private function fillFromTransportPackage(TransportPackageData $transportPackageData, TransportPackage $transportPackage): void
    {
        $transportPackageData->id = $transportPackage->getId();
        $transportPackageData->domainId = $transportPackage->getDomainId();
        $transportPackageData->maxProductPackagesCount = $transportPackage->getMaxProductPackagesCount();
        $transportPackageData->maxWeight = $transportPackage->getMaxWeight();
        $transportPackageData->maxGirth = $transportPackage->getMaxGirth();
        $transportPackageData->priceWithVat = $transportPackage->getPriceWithVat();
        $transportPackageData->dimension1 = $transportPackage->getDimension1();
        $transportPackageData->dimension2 = $transportPackage->getDimension2();
        $transportPackageData->dimension3 = $transportPackage->getDimension3();
    }
}
