<?php

declare(strict_types=1);

namespace App\Model\Cart\Transport;

use App\Model\Transport\Transport;
use Shopsys\FrameworkBundle\Component\Money\Money;

class CartTransportData
{
    /**
     * @var \App\Model\Transport\Transport
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
