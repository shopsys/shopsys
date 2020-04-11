<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;
use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;

/**
 * @method \App\Model\Transport\TransportData create()
 * @method \App\Model\Transport\TransportData createFromTransport(\App\Model\Transport\Transport $transport)
 */
class TransportDataFactory extends BaseTransportDataFactory
{
    /**
     * @return \App\Model\Transport\TransportData
     */
    protected function createInstance(): BaseTransportData
    {
        return new TransportData();
    }
}
