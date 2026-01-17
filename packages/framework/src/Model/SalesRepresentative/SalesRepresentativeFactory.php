<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\SalesRepresentative;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class SalesRepresentativeFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(SalesRepresentativeData $data): SalesRepresentative
    {
        $entityClassName = $this->entityNameResolver->resolve(SalesRepresentative::class);

        return new $entityClassName($data);
    }
}
