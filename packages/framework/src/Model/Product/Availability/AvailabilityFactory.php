<?php

namespace Shopsys\FrameworkBundle\Model\Product\Availability;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class AvailabilityFactory implements AvailabilityFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Product\Availability\AvailabilityData $data
     * @return \Shopsys\FrameworkBundle\Model\Product\Availability\Availability
     */
    public function create(AvailabilityData $data): Availability
    {
        $classData = $this->entityNameResolver->resolve(Availability::class);

        return new $classData($data);
    }
}
