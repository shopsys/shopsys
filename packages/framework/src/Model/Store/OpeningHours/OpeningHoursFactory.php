<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\OpeningHours;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class OpeningHoursFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHoursData $data
     * @return \Shopsys\FrameworkBundle\Model\Store\OpeningHours\OpeningHours
     */
    public function create(OpeningHoursData $data): OpeningHours
    {
        $entityClassName = $this->entityNameResolver->resolve(OpeningHours::class);

        return new $entityClassName($data);
    }
}
