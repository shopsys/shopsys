<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Component\Money\Money;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class CartTransportData
{
    public Transport $transport;

    public Money $watchedPrice;

    public ?string $pickupPlaceIdentifier;
}
