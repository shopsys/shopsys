<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Transfer;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class TransferFactory
{
    public function __construct(
        protected readonly EntityNameResolver $entityNameResolver,
    ) {
    }

    public function create(string $identifier, string $name): Transfer
    {
        $entityName = $this->entityNameResolver->resolve(Transfer::class);

        return new $entityName($identifier, $name);
    }
}
