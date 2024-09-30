<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

class TransportInputPricesData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\PriceWithLimitData[]
     */
    public $pricesWithLimits = [];

    /**
     * @var \Shopsys\FrameworkBundle\Model\Pricing\Vat\Vat|null
     */
    public $vat;
}
