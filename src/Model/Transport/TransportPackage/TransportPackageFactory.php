<?php

declare(strict_types=1);

namespace App\Model\Transport\TransportPackage;

use App\Model\Transport\Transport;

class TransportPackageFactory
{
    /**
     * @param \App\Model\Transport\TransportPackage\TransportPackageData $transportPackageData
     * @param \App\Model\Transport\Transport $transport
     * @return \App\Model\Transport\TransportPackage\TransportPackage
     */
    public function create(TransportPackageData $transportPackageData, Transport $transport): TransportPackage
    {
        return new TransportPackage($transportPackageData, $transport);
    }
}
