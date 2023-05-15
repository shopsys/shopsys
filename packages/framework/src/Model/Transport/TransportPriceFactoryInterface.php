<?php

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;

interface TransportPriceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Transport\Transport $transport
     * @param \Shopsys\FrameworkBundle\Component\Money\Money $price
     * @param int $domainId
     * @return \Shopsys\FrameworkBundle\Model\Transport\TransportPrice
     */
    public function create(
        Transport $transport,
        Money $price,
        int $domainId,
    ): TransportPrice;
}
