<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;

/**
 * @method \App\Model\Transport\TransportData create()
 * @method \App\Model\Transport\TransportData createFromTransport(\App\Model\Transport\Transport $transport)
 * @method fillNew(\App\Model\Transport\TransportData $transportData)
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
}
