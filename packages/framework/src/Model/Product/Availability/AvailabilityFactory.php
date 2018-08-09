<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

class AvailabilityFactory implements AvailabilityFactoryInterface
{
    public function create(AvailabilityData $data): Availability
    {
        return new Availability($data);
    }
}
