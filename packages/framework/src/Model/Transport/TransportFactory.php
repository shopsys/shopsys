<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transport;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class TransportFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(TransportData $data): Transport
    {
        $entityClassName = $this->entityNameResolver->resolve(Transport::class);

        return new $entityClassName($data);
    }
}
