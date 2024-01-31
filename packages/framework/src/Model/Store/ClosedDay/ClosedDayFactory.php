<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Store\ClosedDay;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class ClosedDayFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDayData $data
     * @return \Shopsys\FrameworkBundle\Model\Store\ClosedDay\ClosedDay
     */
    public function create(ClosedDayData $data): ClosedDay
    {
        $entityClassName = $this->entityNameResolver->resolve(ClosedDay::class);

        return new $entityClassName($data);
    }
}
