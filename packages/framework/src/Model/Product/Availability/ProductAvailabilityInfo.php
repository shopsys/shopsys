<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class ProductAvailabilityInfo
{
    public function __construct(
        public readonly string $name,
        public readonly string $status,
    ) {
    }
}
