<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PersistentReferenceFactory
{
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    public function create(
        string $referenceName,
        string $entityName,
        int $entityId,
    ): PersistentReference {
        $entityClassName = $this->entityNameResolver->resolve(PersistentReference::class);

        return new $entityClassName($referenceName, $entityName, $entityId);
    }
}
