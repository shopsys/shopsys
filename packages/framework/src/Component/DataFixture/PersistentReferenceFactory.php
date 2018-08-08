<?php

namespace Shopsys\FrameworkBundle\Component\DataFixture;

class PersistentReferenceFactory implements PersistentReferenceFactoryInterface
{
    public function create(
        string $referenceName,
        string $entityName,
        int $entityId
    ): PersistentReference {
        return new PersistentReference($referenceName, $entityName, $entityId);
    }
}
