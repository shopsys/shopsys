<?php

declare(strict_types=1);

namespace App\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportDataFactory as BaseTransportDataFactory;

/**
 * @method fillFromTransport(\App\Model\Transport\TransportData $transportData, \App\Model\Transport\Transport $transport)
 * @method \App\Model\Transport\TransportData create()
 * @method \App\Model\Transport\TransportData createFromTransport(\App\Model\Transport\Transport $transport)
 * @method \App\Model\Transport\TransportData createInstance()
 * @method fillNew(\App\Model\Transport\TransportData $transportData)
 */
class TransportDataFactory extends BaseTransportDataFactory
{
}
