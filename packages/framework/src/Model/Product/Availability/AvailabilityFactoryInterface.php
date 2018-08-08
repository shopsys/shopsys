<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

interface AvailabilityFactoryInterface
{

    public function create(AvailabilityData $data): Availability;
}
