<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

interface AvailabilityFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function create(AvailabilityData $data): Availability;
}
