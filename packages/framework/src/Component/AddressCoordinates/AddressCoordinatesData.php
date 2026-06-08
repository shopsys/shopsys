<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\AddressCoordinates;

final readonly class AddressCoordinatesData
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {
    }
}
