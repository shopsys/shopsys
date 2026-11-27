<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Cart\Transport;

use Shopsys\FrameworkBundle\Model\Pricing\PriceInterface;
use Shopsys\FrameworkBundle\Model\Transport\Transport;

class CartTransportData
{
    public Transport $transport;

    public PriceInterface $watchedPrice;

    public ?string $pickupPlaceIdentifier;

    /**
     * @var string[] "Y-m-d" dates the selected pickup place is closed on
     */
    public array $pickupPlaceClosedDates = [];
}
