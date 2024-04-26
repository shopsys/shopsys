<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class CartTransportData
{
    /**
     * @var \Shopsys\FrameworkBundle\Model\Transport\Transport
     */
    public Transport $transport;

    /**
     * @var \Shopsys\FrameworkBundle\Component\Money\Money
     */
    public Money $watchedPrice;

    /**
     * @var string|null
     */
    public ?string $pickupPlaceIdentifier;
}
