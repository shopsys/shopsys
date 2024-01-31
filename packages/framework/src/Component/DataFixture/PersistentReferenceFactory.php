<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Component\DataFixture;

use Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver;

class PersistentReferenceFactory implements PersistentReferenceFactoryInterface
{
    /**
     * @param \Shopsys\FrameworkBundle\Component\EntityExtension\EntityNameResolver $entityNameResolver
     */
    public function __construct(protected readonly EntityNameResolver $entityNameResolver)
    {
    }

    /**
     * @param string $referenceName
     * @param string $entityName
     * @param int $entityId
     * @return \Shopsys\FrameworkBundle\Component\DataFixture\PersistentReference
     */
    public function create(
        string $referenceName,
        string $entityName,
        int $entityId,
    ): PersistentReference {
        $entityClassName = $this->entityNameResolver->resolve(PersistentReference::class);

        return new $entityClassName($referenceName, $entityName, $entityId);
    }
}
