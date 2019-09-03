<?php

declare(strict_types=1);

namespace Shopsys\ShopBundle\Model\Transport;

use Shopsys\FrameworkBundle\Model\Transport\TransportData as BaseTransportData;

/**
 * @property \Shopsys\ShopBundle\Model\Payment\Payment[] $payments
 */
class TransportData extends BaseTransportData
{
    public function __construct()
    {
        parent::__construct();
    }
}
