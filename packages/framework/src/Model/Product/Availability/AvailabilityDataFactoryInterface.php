<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

interface AvailabilityDataFactoryInterface
{
    public function create(): AvailabilityData;

    public function createFromAvailability(Availability $availability): AvailabilityData;
}
