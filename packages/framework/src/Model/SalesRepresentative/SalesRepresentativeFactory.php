<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SalesRepresentativeFactory
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    /**
     * @param \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentativeData $data
     * @return \Shopsys\FrameworkBundle\Model\SalesRepresentative\SalesRepresentative
     */
    public function create(SalesRepresentativeData $data): SalesRepresentative
    {
        $entityClassName = $this->entityNameResolver->resolve(SalesRepresentative::class);

        return new $entityClassName($data);
    }
}
