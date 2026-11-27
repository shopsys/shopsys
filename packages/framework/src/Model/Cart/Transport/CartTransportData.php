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
     * @var array{openingHoursOfDays: array<int, array{dayOfWeek: int, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>, exceptionDays: array<int, array{from: string, to: string|null, openingHoursRanges: array<int, array{openingTime: string, closingTime: string}>}>}|null
     */
    public ?array $pickupPlaceOpeningHours;

    public function __construct()
    {
        $this->pickupPlaceOpeningHours = null;
    }
}
