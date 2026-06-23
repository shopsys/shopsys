<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class TransportGroupFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(TransportGroupData $data): TransportGroup
    {
        $entityClassName = $this->entityNameResolver->resolve(TransportGroup::class);

        return new $entityClassName($data);
    }
}
